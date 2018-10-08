<?php

namespace SharengoCore\Entity\Repository;

/**
 * Class ReservationsRepository
 * @package SharengoCore\Entity\Repository
 */
class ServerScriptsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getOldServerScript($dateStart, $dateEnd) {

        $em = $this->getEntityManager();

        $dql = 'SELECT s FROM \SharengoCore\Entity\ServerScripts s '
                . 'WHERE 1=1 '
                . 'AND s.name = :name '
                . 'AND (s.param = :dateStartJson OR (s.startTs >= :dateStartDay AND s.startTs < :dateEndDay)) '
                . 'AND s.note != :status '
                . 'ORDER BY s.id DESC';

        $query = $em->createQuery($dql);
        $query->setParameter('dateStartJson', '{"date": "'.$dateStart.'"}');
        $query->setParameter('dateStartDay', $dateStart);
        $query->setParameter('dateEndDay', $dateEnd);
        $query->setParameter('status', "RUNNING");
        $query->setParameter('name', "addPointDay_scrpit");
        
        return $query->getResult();
        
    }

    /**
     * Return true if a script width $name has a endTs NULL
     *
     * @param string $name
     * @param null $fullPath
     * @return bool
     */
    public function isOpen($name, $fullPath = null, $period = null) {
        $result = false;

        $em = $this->getEntityManager();
        $dql = "SELECT count(s.id) FROM \SharengoCore\Entity\ServerScripts s "
            . "WHERE s.endTs IS NULL AND "
            . "s.name = :name ";

        if(!is_null($fullPath)) {
            $dql .= " AND s.fullPath = :full_path";
        }

        if(!is_null($period)) {
            $dql .= " AND s.startTs >= :period";
        }

        $query = $em->createQuery($dql);

        $query->setParameter('name', $name);

        if(!is_null($fullPath)) {
            $query->setParameter('full_path', $fullPath);
        }

        if(!is_null($period)) {
            $query->setParameter('period', date_create($period));
        }

        if($query->getSingleScalarResult()>0) {
            $result = true;
        }

        return $result;

    }

    /**
     * @param null $name
     * @param null $fullPath
     * @return ServerScripts[]
     */
    public function findOpen($name = null, $fullPath = null) {
        $em = $this->getEntityManager();

        $dql = 'SELECT s FROM \SharengoCore\Entity\ServerScripts s '
            . 'WHERE s.endTs IS NULL ';

        if(!is_null($name)) {
            $dql .= " AND s.name = :name";
        }

        if(!is_null($fullPath)) {
            $dql .= " AND s.fullPath = :full_path";
        }

        $query = $em->createQuery($dql);

        if(!is_null($name)) {
            $query->setParameter('name', $name);
        }

        if(!is_null($fullPath)) {
            $query->setParameter('full_path', $fullPath);
        }

        return $query->getResult();
    }

    /**
     * Check if there is a entity (Trips o ExtraPayments) lock from ascript batch.
     *
     * @param $entity
     * @return bool
     */
    public function isLock($entity) {
        $result = false;

        if($entity instanceof \SharengoCore\Entity\Trips) {
            $lockEntity = "SharengoCore\Entity\Trips";
        } else if($entity instanceof \SharengoCore\Entity\ExtraPayments) {
            $lockEntity = "SharengoCore\Entity\ExtraPayments";
        }
        else {
            return $result;
        }

        $serverScripts = $this->findOpen();

        foreach($serverScripts as $serverScript) {
            if(isset($serverScript->getParam()['lock_entity']) && isset($serverScript->getParam()['lock_id'])) {
                if( $serverScript->getParam()['lock_entity']==$lockEntity) {
                    if (in_array($entity->getId(), $serverScript->getParam()['lock_id'])) {
                        $result = true;
                        break;
                    }
                }
            }
        }

        return $result;

    }
}

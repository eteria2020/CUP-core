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
                . 'AND s.note != :status1 '
                . 'AND s.note != :status2 '
                . 'ORDER BY s.id DESC'
                ;

        $query = $em->createQuery($dql);
        $query->setParameter('dateStartJson', '{"date": "'.$dateStart.'"}');
        $query->setParameter('dateStartDay', $dateStart);
        $query->setParameter('dateEndDay', $dateEnd);
        $query->setParameter('status1', "RUNNING");
        $query->setParameter('status2', "END");
        $query->setParameter('name', "addPointDay_scrpit");
        
        return $query->getResult();
        
    }
}

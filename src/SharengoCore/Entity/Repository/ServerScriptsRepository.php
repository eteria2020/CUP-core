<?php

namespace SharengoCore\Entity\Repository;

/**
 * Class ReservationsRepository
 * @package SharengoCore\Entity\Repository
 */
class ServerScriptsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getOldServerScript($dateStart) {

        $em = $this->getEntityManager();

        $dql = 'SELECT s FROM \SharengoCore\Entity\ServerScripts s '
                . 'WHERE 1=1 '
                . 'and s.id = 40'/*
                . 'AND s.name = :name '
                . 'AND (s.param = :dateStartJson OR s.startTs = :dateStartTs)'
                */;

        $query = $em->createQuery($dql);
        //$query->setParameter('dateStartJson', '{"date": "'.$dateStart.'"}');
        //$query->setParameter('dateStartTs', $dateStart);
        ///$query->setParameter('name', "addPointDay_scrpit");
        
        return $query->getResult();
        
    }
}

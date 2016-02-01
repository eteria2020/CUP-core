<?php

namespace SharengoCore\Entity\Repository;
use Doctrine\ORM\Query;

/**
 * CardsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CardsRepository extends \Doctrine\ORM\EntityRepository
{
    public function ajaxCardCodeAutocomplete($q)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT c FROM \SharengoCore\Entity\Cards c ' .
               'WHERE c.isAssigned = false AND c.assignable = true AND (LOWER(c.rfid) LIKE :value OR LOWER(c.code) LIKE :value)';

        $query = $em->createQuery();

        $query->setParameter('value', strtolower("%" . $q . "%"));
        $query->setDql($dql);
        return $query->getResult();
    }

    public function getLastCardRfid()
    {
        $s_query = "SELECT substring(rfid, 5)::INT as LastRfid FROM cards WHERE rfid ILIKE 'CARD%' ORDER BY LastRfid DESC LIMIT 1";
        $query = $this->getEntityManager()->getConnection()->query($s_query);
        return $query->fetch();
    }

    public function getTotalCards()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(c.rfid) FROM \SharengoCore\Entity\Cards c');
        return $query->getSingleScalarResult();
    }
}

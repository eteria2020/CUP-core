<?php

namespace SharengoCore\Entity\Repository;

class CartasiCsvFileRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAll()
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvFile ccf
            ORDER BY ccf.id ASC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}

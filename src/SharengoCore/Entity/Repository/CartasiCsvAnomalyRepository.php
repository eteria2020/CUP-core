<?php

namespace SharengoCore\Entity\Repository;

class CartasiCsvAnomalyRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllResolved()
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvAnomaly ccf
            WHERE ccf.resolved = true
            ORDER BY ccf.id ASC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllUnresolved()
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT ccf
            FROM \SharengoCore\Entity\CartasiCsvAnomaly ccf
            WHERE ccf.resolved = false
            ORDER BY ccf.id ASC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}

<?php

namespace SharengoCore\Entity\Repository;


class PenaltiesRepository extends \Doctrine\ORM\EntityRepository
{
    public function findById($id)
    {
       $em = $this->getEntityManager();

        $dql = 'SELECT p '
                . 'FROM \SharengoCore\Entity\Penalty p '
                . 'WHERE p-id = :id'
                ;

        $query = $em->createQuery($dql);
        $query->setParameter('id', $id);

        return $query->getResult();
    }
}

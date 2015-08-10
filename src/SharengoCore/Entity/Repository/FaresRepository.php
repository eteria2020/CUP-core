<?php

namespace SharengoCore\Entity\Repository;

class FaresRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOne()
    {
        return $this->createQueryBuilder('f')
            ->select()
            ->getQuery()
            ->getSingleResult();
    }
}

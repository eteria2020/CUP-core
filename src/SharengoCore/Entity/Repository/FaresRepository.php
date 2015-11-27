<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class FaresRepository extends EntityRepository
{
    public function findOne()
    {
        return $this->createQueryBuilder('f')
            ->select()
            ->orderBy('f.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}

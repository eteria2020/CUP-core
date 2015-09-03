<?php

namespace SharengoCore\Entity\Repository;

class CustomersBonusPackagesRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAvailableBonusPackages()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT p
            FROM \SharengoCore\Entity\CustomersBonusPackages
            WHERE p.buyableUntil < CURRENT_TIMESTAMP()
            ORDER BY p.insertedTs DESC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}

<?php

namespace SharengoCore\Entity\Repository;

class CustomersBonusPackagesRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAvailableBonusPackages()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT p
            FROM \SharengoCore\Entity\CustomersBonusPackages p
            WHERE p.buyableUntil > CURRENT_TIMESTAMP()
            ORDER BY p.displayPriority DESC, p.minutes ASC';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}

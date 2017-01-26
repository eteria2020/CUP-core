<?php

namespace SharengoCore\Entity\Repository;

// Internals
use SharengoCore\Entity\Fleet;
// Externals
use Doctrine\ORM\EntityRepository;

class PoisRepository extends EntityRepository
{

    public function getTotalPois()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(p.id) FROM \SharengoCore\Entity\Pois p');
        return $query->getSingleScalarResult();
    }

    public function findByFleet(Fleet $fleet)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT p
        FROM \SharengoCore\Entity\Pois p
        LEFT JOIN \SharengoCore\Entity\Fleet f WITH LOWER(p.town) = LOWER(f.name)
        WHERE f = :fleet";

        $query = $em->createQuery($dql);
        $query->setParameter('fleet', $fleet);

        return $query->getResult();
    }
}

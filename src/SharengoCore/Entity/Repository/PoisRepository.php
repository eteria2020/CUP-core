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
    
    public function verifyPoisBonusPark(Fleet $fleet, $latitude, $longitude, $radius)
    {
            $dql =  "SELECT p FROM \SharengoCore\Entity\Pois p ".
                    "LEFT JOIN \SharengoCore\Entity\Fleet f WITH LOWER(p.town) = LOWER(f.name) ".
                    //radius 100m -> 0.001242
                    "WHERE f = :fleet ".
                    "AND ABS(p.lat - :latitude) <= :radius AND ABS(p.lon - :longitude) <= :radius";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('fleet', $fleet);
        $query->setParameter('radius', $radius);
        $query->setParameter('latitude', $latitude);
        $query->setParameter('longitude',  $longitude);
        //$query->setParameter('id',  $idPois);
        return $query->getResult();
    }
}

<?php

namespace SharengoCore\Entity\Repository;

// Internals
use SharengoCore\Entity\Fleet;
// Externals
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

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
    
    public function verifyPoisBonusPark($fleet, $latitude, $longitude, $radius)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT pois.id as id FROM pois ".
                "LEFT JOIN fleets ON (LOWER(pois.town) = LOWER(fleets.name)) ".
                "WHERE fleets.id=:fleet ";
                //if($fleet == 1 || $fleet == 2 ) {
                    $sql .= "AND LOWER(pois.type) = LOWER('Isole Digitali') ";
                //}
                //if ($fleet == 3) {
                //    $sql .= "AND LOWER(pois.type) != LOWER('Stazione ENEL Drive') ";
                //}
        $sql .="AND ST_Distance_Sphere(ST_MakePoint(:longitude, :latitude), ST_MakePoint(pois.lon,pois.lat)) < :radius";
        
        /*$dql =  "SELECT p FROM \SharengoCore\Entity\Pois p ".
                    "LEFT JOIN \SharengoCore\Entity\Fleet f WITH LOWERpp.town) = LOWER(f.name) ".
                    //radius 100m -> 0.001242
                    "WHERE f = :fleet ".
                    "AND ABS(p.lat - :latitude) <= :radius AND ABS(p.lon - :longitude) <= :radius";
        */
        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('id', 'id');
        $query = $em->createNativeQuery($sql, $rsm);
        //$query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('fleet', $fleet);
        $query->setParameter('radius', $radius);
        $query->setParameter('longitude', $longitude);
        $query->setParameter('latitude', $latitude);
        return $query->getResult();
    }
}

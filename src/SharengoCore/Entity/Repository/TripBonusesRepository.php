<?php

namespace SharengoCore\Entity\Repository;

class TripBonusesRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteTripBonusesByTripIds($tripIds)
    {
        $dql = "DELETE FROM \SharengoCore\Entity\TripBonuses tb ".
            "WHERE tb.trip IN (:ids)";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('ids', $tripIds);

        return $query->execute();
    }
    
    public function findByTripId($tripId) {
        
        $dql = "SELECT tb FROM \SharengoCore\Entity\TripBonuses tb ".
            "WHERE tb.trip = :id";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('id', $tripId);

        return $query->execute();
    }
}

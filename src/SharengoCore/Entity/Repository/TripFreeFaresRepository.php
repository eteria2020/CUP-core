<?php

namespace SharengoCore\Entity\Repository;

class TripFreeFaresRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteTripFreeFaresByTripIds($tripIds)
    {
        $dql = "DELETE FROM \SharengoCore\Entity\TripFreeFares tb ".
            "WHERE tb.trip IN (:ids)";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('ids', $tripIds);

        return $query->execute();
    }
    
    public function findByTripId($tripId) {
        
        $dql = "SELECT tb FROM \SharengoCore\Entity\TripFreeFares tb ".
            "WHERE tb.trip = :id";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('id', $tripId);

        return $query->execute();
        
    }
}

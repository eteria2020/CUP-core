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
}

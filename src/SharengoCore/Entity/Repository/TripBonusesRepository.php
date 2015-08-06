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
}

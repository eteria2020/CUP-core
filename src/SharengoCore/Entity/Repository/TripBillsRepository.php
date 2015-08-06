<?php

namespace SharengoCore\Entity\Repository;

class TripBillsRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteTripBillsByTripIds($tripIds)
    {
        $dql = "DELETE FROM \SharengoCore\Entity\TripBills tb ".
            "WHERE tb.trip IN (:ids)";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('ids', $tripIds);

        return $query->execute();
    }
}

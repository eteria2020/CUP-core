<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;

/**
 * TripsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TripsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTripsByCustomer($customerId)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT t FROM \SharengoCore\Entity\Trips t WHERE t.customer = :id"
        );
        $query->setParameter('id', $customerId);

        return $query->getResult();
    }

    public function findTripsByPlateNotEnded($plate)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT trip, car, cust, card
        FROM \SharengoCore\Entity\Trips trip
        JOIN trip.car car
        JOIN trip.customer cust
        JOIN cust.card card
        WHERE trip.car = :id AND trip.timestampEnd IS NULL";

        $query = $em->createQuery($dql);
        $query->setParameter('id', $plate);

        return $query->getResult();
    }

    public function findTripsByCustomerNotEnded($customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT trip, car, cust, card
        FROM \SharengoCore\Entity\Trips trip
        JOIN trip.car car
        JOIN trip.customer cust
        JOIN cust.card card
        WHERE trip.customer = :id AND trip.timestampEnd IS NULL";

        $query = $em->createQuery($dql);
        $query->setParameter('id', $customer);

        return $query->getResult();
    }

    public function getTotalTrips()
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT COUNT(t.id) FROM \SharengoCore\Entity\Trips t'
        );
        return $query->getSingleScalarResult();
    }

    public function findTripsToBeAccounted()
    {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t ".
            "WHERE t.isAccounted = false ".
            "AND t.timestampEnd IS NOT NULL ".
            "AND t.timestampEnd >= t.timestampBeginning ".
            "ORDER BY t.timestampEnd ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    public function findCustomerTripsToBeAccounted(Customers $customer)
    {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t ".
            "WHERE t.isAccounted = false ".
            "AND t.timestampEnd IS NOT NULL ".
            "AND t.timestampEnd >= t.timestampBeginning ".
            "AND t.customer = :customer ".
            "ORDER BY t.timestampEnd ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('customer', $customer);
        //$query->setMaxResults(10);
        return $query->getResult();
    }

    public function findLastTrip($plate)
    {
        $dql = "SELECT t
        FROM \SharengoCore\Entity\Trips t
        WHERE t.car = :plate
        AND t.timestampEnd IS NOT NULL
        ORDER BY t.timestampEnd DESC";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('plate', $plate);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function findTripsByUsersInGoldList()
    {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t ".
            "JOIN t.customer c ".
            "WHERE c.goldList = true";

        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    /**
     * update the payable field of the selected trips
     *
     * @param int[] an array of trip Ids
     * @param boolean wether to set the trips payable or not
     */
    public function updateTripsPayable($tripIds, $payable)
    {
        $dql = "UPDATE \SharengoCore\Entity\Trips t ".
            "SET t.payable = :payable ".
            "WHERE t.id IN (:tripIds)";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('payable', $payable);
        $query->setParameter('tripIds', $tripIds);

        return $query->execute();
    }

    /**
     * selects the trips that are already accounted but still need to be
     * processed for the cost computation
     * At the moment this means that the trip isAccounted but has not a linked
     * record in the trip_payments table
     *
     * @return Trips[]
     */
    public function findTripsForCostComputation()
    {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t ".
            "LEFT JOIN t.tripPayments tp ".
            "JOIN t.customer c ".
            "WHERE t.isAccounted = true ".
            "AND tp.id IS NULL ".
            "AND c.paymentAble = TRUE ".
            "AND t.payable = FALSE";

        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }
}

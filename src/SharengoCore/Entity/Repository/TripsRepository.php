<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Trips;

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
            "WHERE t.isAccounted = false ". // trips that were not already accounted
            "AND t.timestampEnd IS NOT NULL ". // not trips still running
            "AND t.timestampEnd >= t.timestampBeginning ". // only trips with positive length
            "AND t.timestampBeginning >= :firstJanuary2015 ". // only trips begun after 01/01/2015
            "AND t.timestampEnd - t.timestampBeginning <= :oneDay ". // length less than one day
            "AND t.payable = TRUE ". //only payable trips
            "ORDER BY t.timestampEnd ASC"; // old trips first
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('firstJanuary2015', date_create('2015-01-01'));
        $query->setParameter('oneDay', '24:00:00');

        return $query->getResult();
    }

    public function findCustomerTripsToBeAccounted(Customers $customer)
    {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t ".
            "WHERE t.isAccounted = false ".
            "AND t.timestampEnd IS NOT NULL ".
            "AND t.timestampEnd >= t.timestampBeginning ".
            "AND t.timestampBeginning >= :firstJanuary2015 ". // only trips begun after 01/01/2015
            "AND t.timestampEnd - t.timestampBeginning <= :oneDay ". // length less than one day
            "AND t.payable = TRUE ". //only payable trips
            "AND t.customer = :customer ".
            "ORDER BY t.timestampEnd ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('firstJanuary2015', date_create('2015-01-01'));
        $query->setParameter('oneDay', '24:00:00');
        $query->setParameter('customer', $customer);

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
     * @return mixed
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
            "LEFT JOIN t.tripPayment tp ".
            "JOIN t.customer c ".
            "WHERE t.isAccounted = true ". //only trips that were already processed by the accounting trips
            "AND t.costComputed = false ". //only trips that were not already processed by the cost computing script
            "AND tp.id IS NULL ". // only trips that do not have a trip payment
            "AND t.payable = TRUE ". // trip is payable
            "AND c.paymentAble = TRUE"; // customer is paymentAble

        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    /**
     * Exclude trips less than 5 mins long
     *
     * @param Customers $customer
     * @return mixed
     */
    public function findDistinctDatesForCustomerByMonth($customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT DISTINCT t.timestampBeginning
        FROM \SharengoCore\Entity\Trips t
        WHERE t.customer = :customer
        AND t.timestampEnd IS NOT NULL
        AND t.timestampEnd - t.timestampBeginning >= :fiveMinutes
        ORDER BY t.timestampBeginning DESC";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        $query->setParameter('fiveMinutes', '00:05:00');

        return $query->getResult();
    }

    public function findListTripsForMonthByCustomer($date, $customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT t
        FROM \SharengoCore\Entity\Trips t
        LEFT JOIN \SharengoCore\Entity\TripPayments tp
        WITH t.id = tp.trip
        LEFT JOIN \SharengoCore\Entity\TripBonuses tb
        WITH t.id = tb.trip
        LEFT JOIN \SharengoCore\Entity\TripFreeFares tf
        WITH t.id = tf.trip
        WHERE t.customer = :customer
        AND t.timestampBeginning >= :monthStart
        AND t.timestampBeginning < :monthEnd
        ORDER BY t.timestampBeginning";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        $query->setParameter('monthStart', $date);

        $date = date_create($date);
        $year = intval($date->format('Y'));
        $month = intval($date->format('m')) + 1;
        if ($month == 13) {
            $month = 1;
            $year ++;
        }
        $month = ($month < 10) ? '0' . $month : $month;

        $query->setParameter('monthEnd', date_create($year . '-' . $month . '-01 0:00:00'));

        return $query->getResult();
    }

    /**
     * @param integer $limit
     * @return Trips[]
     */
    public function findTripsNoAddress($limit)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT DISTINCT t
        FROM \SharengoCore\Entity\Trips t
        WHERE t.timestampEnd IS NOT NULL
        AND (t.addressBeginning IS NULL OR t.addressEnd IS NULL)
        AND t.longitudeEnd IS NOT NULL
        AND t.latitudeEnd IS NOT NULL
        AND t.longitudeBeginning != 0
        AND t.latitudeBeginning != 0
        AND t.payable = true
        ORDER BY t.timestampBeginning ASC";

        $query = $em->createQuery($dql);
        if ($limit != 0) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    /**
     * close a trip setting timestampEnd and payable
     *
     * @param Trips $trip
     * @param \DateTime $timestampEnd
     * @param bool $payable
     */
    public function closeTrip(
        Trips $trip,
        \Datetime $timestampEnd,
        $payable
    ) {
        $trip->setTimestampEnd($timestampEnd);
        $trip->setPayable($payable);

        $this->getEntityManager()->persist($trip);
        $this->getEntityManager()->flush();
    }


    public function getTotalTripsNotPayed()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT COUNT(e.id)
        FROM \SharengoCore\Entity\Trips e
        INNER JOIN e.customer cu
        INNER JOIN e.fleet f
        INNER JOIN e.car c
        INNER JOIN cu.card cc
        INNER JOIN e.tripPayment tp
        WHERE cu.goldList = false
        AND e.payable = true AND
        e.timestampEnd IS NOT NULL AND
        (e.timestampEnd - e.timestampBeginning) >= (DATE_ADD(CURRENT_TIMESTAMP(), 300, 'second') - CURRENT_TIMESTAMP()) AND
        tp.payedSuccessfullyAt IS NULL ";

        $query = $em->createQuery($dql);
        return $query->getSingleScalarResult();
    }
}

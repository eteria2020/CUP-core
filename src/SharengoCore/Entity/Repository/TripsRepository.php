<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Cars;

/**
 * TripsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TripsRepository extends \Doctrine\ORM\EntityRepository {

    public function findTripsByCustomer($customerId) {
        $query = $this->getEntityManager()->createQuery(
                "SELECT t FROM \SharengoCore\Entity\Trips t WHERE t.customer = :id"
        );
        $query->setParameter('id', $customerId);

        return $query->getResult();
    }

    public function findTripsByCustomerCO2($customerId) {
        $query = $this->getEntityManager()->createQuery(
                "SELECT t FROM \SharengoCore\Entity\Trips t WHERE t.customer = :id "
                . "AND t.timestampEnd IS NOT NULL AND t.endTx IS NOT NULL"
        );
        $query->setParameter('id', $customerId);

        return $query->getResult();
    }

    public function getTripsByCustomerForAddPointYesterday($customerId, $dateYesterdayStart, $dateTodayStart) {

        $em = $this->getEntityManager();

        $dql = 'SELECT t '
                . 'FROM \SharengoCore\Entity\Trips t '
                . 'WHERE 1=1 '
                . 'AND t.endTx < :dateTodayStart '
                . 'AND t.endTx >= :dateYesterdayStart '
                . 'AND t.timestampEnd IS NOT NULL '
                . 'AND t.customer = :customerId '
                . 'AND t.payable = :payable '
                . 'AND t.pinType IS NULL '
                . 'AND t.timestampBeginning > :date '
        ;

        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateYesterdayStart', $dateYesterdayStart);
        $query->setParameter('dateTodayStart', $dateTodayStart);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('payable', $payable);
        $query->setParameter('date', '2017-09-18');

        return $query->getResult();
    }

    public function getTripsByCustomerForAddPointMonth($customerId, $dateCurrentMonthStart, $dateYesterdayStart) {
        $em = $this->getEntityManager();

        $dql = 'SELECT t '
                . 'FROM \SharengoCore\Entity\Trips t '
                . 'WHERE '
                . 't.endTx >= :dateCurrentMonthStart '
                . 'AND t.endTx < :dateYesterdayStart '
                . 'AND t.customer = :customerId '
                . 'AND t.payable = :payable '
                . 'AND t.pinType IS NULL '
                . 'AND t.beginningTx > :date '
                . 'AND t.endTx >= :dateInitAssignPoints';

        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateCurrentMonthStart', $dateCurrentMonthStart);
        $query->setParameter('dateYesterdayStart', $dateYesterdayStart);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('payable', $payable);
        $query->setParameter('date', '2015-01-01');
        $query->setParameter('dateInitAssignPoints', '2017-09-18 00:00:00');

        return $query->getResult();
    }

    public function findTripsByPlateNotEnded($plate) {
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

    public function findTripsByCustomerNotEnded($customer) {
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

    public function getTotalTrips() {
        $query = $this->getEntityManager()->createQuery(
                'SELECT COUNT(t.id) FROM \SharengoCore\Entity\Trips t'
        );
        return $query->getSingleScalarResult();
    }

    public function findTripsToBeAccounted() {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "WHERE t.isAccounted = false " . // trips that were not already accounted
                "AND t.timestampEnd IS NOT NULL " . // not trips still running
                "AND t.timestampEnd >= t.timestampBeginning " . // only trips with positive length
                "AND t.timestampBeginning >= :firstJanuary2015 " . // only trips begun after 01/01/2015
                "AND t.timestampEnd - t.timestampBeginning <= :oneDay " . // length less than one day
                "AND t.payable = TRUE " . //only payable trips
                "ORDER BY t.timestampEnd ASC"; // old trips first
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('firstJanuary2015', date_create('2015-01-01'));
        $query->setParameter('oneDay', '24:00:00');

        return $query->getResult();
    }

    /**
     * selects the trips that need to be
     * processed for the bonus computation
     *
     * @return Trips[]
     */
    public function findTripsForBonusComputation() {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                //t.isAccounted = true ". //only trips that were already processed by the accounting trips
                "WHERE t.bonusComputed = false " . //only trips that were not already processed by the bonus computing script
                "AND t.parkSeconds > 0 " . //only trips with parking time
                "AND t.timestampEnd IS NOT NULL " . //only trips finished
                "ORDER BY t.timestampEnd ASC";

        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    /**
     * selects the trips that need to be
     * processed for the extra fares
     *
     * @return Trips[]
     */
    public function findTripsForExtraFareToBePayed() {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "INNER JOIN t.tripPayment tp " .
                "WHERE tp.status = :status " .
                "AND t.endTx > :midnight " . //only trips that endTx from midnight
                "ORDER BY t.endTx ASC";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('status', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('midnight', date_create()->format('Y-m-d') . ' 00:00:00');

        return $query->getResult();
    }

    public function findTripsForExtraFareNullTripPayments() {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "LEFT JOIN t.tripPayment tp " .
                "WHERE tp.id IS NULL " .
                "AND t.payable = true " .
                "AND t.endTx IS NOT NULL " .
                "AND t.endTx > :midnight " . //only trips that endTx from midnight
                "ORDER BY t.endTx ASC";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('midnight', date_create()->format('Y-m-d') . ' 00:00:00');

        return $query->getResult();
    }

    /**
     * selects the trips that need to be
     * processed for the bonus computation park
     * at a given date
     *
     * @return Trips[]
     */
    public function findTripsForBonusParkComputation($datestamp, $carplate) {
        $dateStart = date_create($datestamp . ' 00:00:00');
        $dateEnd = date_create($datestamp . ' 23:59:59');

        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "LEFT JOIN \SharengoCore\Entity\TripPayments tp WITH t.id = tp.trip " .
                "WHERE t.timestampEnd >= :dateStart AND t.timestampEnd <= :dateEnd " . //date
                "AND t.fleet != 3 "; //only Milan & Florence
        if (($carplate != 'all')) {
            $dql .= "AND t.car IN ('DD30908', 'EG35685', 'EG35649') ";
        }
        $dql .= "AND tp.status = :status " .
                "AND t.timestampEnd IS NOT NULL " . //only trips finished
                "AND t.batteryEnd IS NOT NULL AND t.batteryEnd < 25 " . //battery level end trip
                "AND t.longitudeEnd > 0 AND t.latitudeEnd > 0 " .
                "ORDER BY t.timestampEnd ASC";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('status', "invoiced");
        $query->setParameter('dateStart', date_sub($dateStart, date_interval_create_from_date_string('1 days')));
        $query->setParameter('dateEnd', date_sub($dateEnd, date_interval_create_from_date_string('1 days')));
        return $query->getResult();
    }

    /**
     *
     * @param Trips $trip
     * @return Business business
     */
    public function findBusinessByTrip(Trips $trip) {
        $em = $this->getEntityManager();

        $dql = "SELECT b
        FROM \BusinessCore\Entity\Business b
        INNER JOIN \BusinessCore\Entity\BusinessTrip bt WITH bt.business = b
        WHERE bt.trip = :trip";

        $query = $em->createQuery($dql);
        $query->setParameter('trip', $trip);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     *
     * @param Trips $trip
     * @return BusinessFare BusinessFare
     */
    public function findBusinessFareByTrip(Trips $trip) {
        $em = $this->getEntityManager();

        $dql = "SELECT bf
        FROM \BusinessCore\Entity\BusinessFare bf
        INNER JOIN \BusinessCore\Entity\BusinessTrip bt WITH bt.business = bf.business
        WHERE bt.trip = :trip
        AND bf.insertedTs < :timestampBeginning
        ORDER BY bf.insertedTs DESC";

        $query = $em->createQuery($dql);
        $query->setParameter('trip', $trip);
        $query->setParameter('timestampBeginning', $trip->getTimestampBeginning()->format("Y-m-d H:i:s"));
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * Return a BusinessTripPayment from a Trip (public)
     *
     * @param Trips $trip
     * @return BusinessTripPayment businessTripPayment
     */
    public function findBusinessTripPaymentByTrip(Trips $trip) {
        $em = $this->getEntityManager();

        $dql = "SELECT btp
        FROM \BusinessCore\Entity\BusinessTripPayment btp
        INNER JOIN \BusinessCore\Entity\BusinessTrip bt WITH bt = btp.businessTrip
        WHERE bt.trip = :trip";

        $query = $em->createQuery($dql);
        $query->setParameter('trip', $trip);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * Return a BusinessInvoice from a Trip (public)
     *
     * @param Trips $trip
     * @return type
     */
    public function findBusinessInvoiceByTrip(Trips $trip) {
        $em = $this->getEntityManager();

        $dql = "SELECT bi
        FROM \BusinessCore\Entity\BusinessTripPayment btp
        INNER JOIN \BusinessCore\Entity\BusinessTrip bt WITH bt = btp.businessTrip
        INNER JOIN \BusinessCore\Entity\BusinessInvoice bi WITH bi = btp.businessInvoice
        WHERE bt.trip = :trip";

        $query = $em->createQuery($dql);
        $query->setParameter('trip', $trip);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function findCustomerTripsToBeAccounted(Customers $customer) {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "WHERE t.isAccounted = false " .
                "AND t.timestampEnd IS NOT NULL " .
                "AND t.timestampEnd >= t.timestampBeginning " .
                "AND t.timestampBeginning >= :firstJanuary2015 " . // only trips begun after 01/01/2015
                "AND t.timestampEnd - t.timestampBeginning <= :oneDay " . // length less than one day
                "AND t.payable = TRUE " . //only payable trips
                "AND t.customer = :customer " .
                "ORDER BY t.timestampEnd ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('firstJanuary2015', date_create('2015-01-01'));
        $query->setParameter('oneDay', '24:00:00');
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }

    public function findLastTrip($plate) {
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

    public function findTripsByUsersInGoldList() {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "JOIN t.customer c " .
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
    public function updateTripsPayable($tripIds, $payable) {
        $dql = "UPDATE \SharengoCore\Entity\Trips t " .
                "SET t.payable = :payable " .
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
    public function findTripsForCostComputation() {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                "LEFT JOIN t.tripPayment tp " .
                "JOIN t.customer c " .
                "WHERE t.isAccounted = true " . //only trips that were already processed by the accounting trips
                "AND t.costComputed = false " . //only trips that were not already processed by the cost computing script
                "AND tp.id IS NULL " . // only trips that do not have a trip payment
                "AND t.payable = TRUE "; // trip is payable

        $query = $this->getEntityManager()->createQuery($dql);
        return $query->getResult();
    }

    /**
     * Exclude trips less than 5 mins long
     *
     * @param Customers $customer
     * @return mixed
     */
    public function findDistinctDatesForCustomerByMonth($customer) {
        $em = $this->getEntityManager();

        $dql = "SELECT DISTINCT t.timestampBeginning
        FROM \SharengoCore\Entity\Trips t
        WHERE t.customer = :customer
        AND t.timestampEnd IS NOT NULL
        AND t.timestampEnd - t.timestampBeginning >= :oneMinute
        ORDER BY t.timestampBeginning DESC";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        $query->setParameter('oneMinute', '00:01:00');

        return $query->getResult();
    }

    public function findListTripsForMonthByCustomer($date, $customer) {
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
    public function findTripsNoAddress($limit) {
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
    Trips $trip, \Datetime $timestampEnd, $payable
    ) {
        $trip->setTimestampEnd($timestampEnd);
        $trip->setPayable($payable);

        $this->getEntityManager()->persist($trip);
        $this->getEntityManager()->flush();
    }

    public function getTotalTripsNotPayed() {
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
        (e.timestampEnd - e.timestampBeginning) >= (DATE_ADD(CURRENT_TIMESTAMP(), 60, 'second') - CURRENT_TIMESTAMP()) AND
        tp.payedSuccessfullyAt IS NULL ";

        $query = $em->createQuery($dql);
        return $query->getSingleScalarResult();
    }

    public function updateTripsAdrress(Trips $trip, $addressBeginning, $addressEnd) {
        $dql = "UPDATE \SharengoCore\Entity\Trips t " .
                "SET t.addressBeginning = :addressBeginning, " .
                "t.addressEnd = :addressEnd " .
                "WHERE t = :trip";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('addressBeginning', $addressBeginning);
        $query->setParameter('addressEnd', $addressEnd);
        $query->setParameter('trip', $trip);

        return $query->execute();
    }

    /**
     * @param Trips $trip
     * @return Trip[]
     */
    public function findPreviousTrip(Trips $trip) {
        $em = $this->getEntityManager();

        $dql = "SELECT t
        FROM \SharengoCore\Entity\Trips t
        WHERE t.car = :plate
        AND t.timestampBeginning < (SELECT tr.timestampBeginning FROM \SharengoCore\Entity\Trips tr WHERE tr.id = :tripId)
        ORDER BY t.timestampBeginning DESC";

        $query = $em->createQuery($dql);
        $query->setParameter('tripId', $trip->getId());
        $query->setParameter('plate', $trip->getCar()->getPlate());
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }

    /**
     * @param Customer|null $customer
     * @param datetime|null $timestampEndParam
     * @return Trip[]
     */
    public function findTripsToBePayedAndWrong(Customers $customer = null, $timestampEndParam = null) {
        $em = $this->getEntityManager();

        $dql = 'SELECT t FROM SharengoCore\Entity\Trips t ' .
                'JOIN t.tripPayment tp ' .
                'JOIN t.customer c ' .
                'WHERE t.payable = true ' .
                'AND tp.status IN (:status_to_be_payed, :status_wrong) ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }
        if ($timestampEndParam !== null) {
            $dql .= 'AND t.timestampEnd >= :timestampEndParam ';
        }

        $dql .= ' ORDER BY t.timestampBeginning ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status_to_be_payed', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('status_wrong', TripPayments::STATUS_WRONG_PAYMENT);
        //$query->setParameter('midnight', date_create('midnight'));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($timestampEndParam !== null) {
            $query->setParameter('timestampEndParam', date_create($timestampEndParam));
        }

        return $query->getResult();
    }

    public function findFirstTripInvoicedByCustomer($customer) {
        $dql = "SELECT t FROM \SharengoCore\Entity\Trips t " .
                //"LEFT JOIN \SharengoCore\Entity\TripPayments tp WITH t.id = tp.trip ".
                "WHERE t.timestampEnd IS NOT NULL AND t.payable = TRUE " .
                "AND t.customer = :customer " . //AND tp.status = :status
                "ORDER BY t.timestampEnd ASC ";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('customer', $customer);
        //$query->setParameter('status', 'invoiced');
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * Return the trips: maintainer, open and more conditions
     *
     * @param type $beginningIntervalMinute
     * @param type $lastContactIntervalMinutes
     * @param type $additionalConditions
     * @return type
     */
    public function findTripsForCloseOldTripMaintainer($beginningIntervalMinute = null, $lastContactIntervalMinutes = null, $additionalConditions = null) {

        $em = $this->getEntityManager();

        $dql = "SELECT DISTINCT t "
                . "FROM \SharengoCore\Entity\Trips t "
                . "JOIN t.customer cu "
                . "JOIN t.car ca "
                . "WHERE cu.maintainer = true "
                . "AND t.timestampEnd IS NULL ";

        $now = new \DateTime();
        if (!is_null($beginningIntervalMinute)) {
            $dateLastBeginning = $now->modify($beginningIntervalMinute);
            $dql .= " AND t.timestampBeginning < :lastBeginning ";
        }

        if (!is_null($lastContactIntervalMinutes)) {
            $dateLastContact = $now->modify($lastContactIntervalMinutes);
            $dql .= " AND ca.lastContact > :lastContact ";
        }

        if (!is_null($additionalConditions)) {
            $dql .= " " . $additionalConditions;
        }

        $query = $em->createQuery($dql);
        if (!is_null($beginningIntervalMinute)) {
            $query->setParameter('lastBeginning', $dateLastBeginning);
        }

        if (!is_null($lastContactIntervalMinutes)) {
            $query->setParameter('lastContact', $dateLastContact);
        }

        return $query->getResult();
    }

     public function findTripsOpenByCar(Cars $car){
        $em = $this->getEntityManager();

        $dql= "SELECT t "
            . "FROM \SharengoCore\Entity\Trips t "
            . "WHERE t.car = :car "
            . "AND t.timestampEnd IS NULL "
            . "ORDER BY t.id";

        $query = $em->createQuery($dql);
        $query->setParameter('car', $car);
        return $query->getResult();
     }

    public function getTripInMonth($customerId, $dateStart, $dateEnd) {

        $em = $this->getEntityManager();

        $dql = 'SELECT t '
                . 'FROM \SharengoCore\Entity\Trips t '
                . 'WHERE 1=1 '
                . 'AND t.endTx < :dateEnd '
                . 'AND t.endTx >= :dateStart '
                . 'AND t.timestampEnd IS NOT NULL '
                . 'AND t.customer = :customerId '
                . 'AND t.payable = :payable '
                . 'AND t.pinType IS NULL '
                . 'AND t.timestampEnd < :date2 '
                . 'AND t.timestampBeginning > :date1 '
                . 'AND (t.timestampEnd - t.timestampBeginning) < :oneDay '
                . 'ORDER BY t.id'
        ;

        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateStart', $dateStart);
        $query->setParameter('dateEnd', $dateEnd);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('payable', $payable);
        $query->setParameter('date1', '2017-09-18');
        $query->setParameter('date2', '2018-01-01');
        $query->setParameter('oneDay', '24:00:00');

        return $query->getResult();
    }

}

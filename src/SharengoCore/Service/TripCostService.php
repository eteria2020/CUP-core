<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\Customers;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManager;

class TripCostService
{
    /**
     * @var FaresService
     */
    private $faresService;

    /**
     * @var TripFaresService
     */
    private $tripFaresService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var boolean
     */
    private $avoidPersistance = true;

    public function __construct(
        FaresService $faresService,
        TripFaresService $tripFaresService,
        EntityManager $entityManager
    ) {
        $this->faresService = $faresService;
        $this->tripFaresService = $tripFaresService;
        $this->entityManager = $entityManager;
    }

    /**
     * process a trip to compute its cost and writes it to database in the
     * trip_payments and the trip_payments_tries tables
     * the three boolean parameters allow the run the function without side effects
     *
     * @param Trips $trip
     * @param boolean $avoidPersistance
     */
    public function computeTripCost(
        Trips $trip,
        $avoidPersistance = true
    ) {
        if ($trip->customerIsPaymentAble()) {
            $tripPayment = $this->retrieveTripCost($trip);

            // in this way all the trips with cost = 0 are retrieved every time by the script
            // this makes the script slower
            // in the future consider if and how to fix this
            if ($tripPayment->getTotalCost() > 0) {
                $this->entityManager->getConnection()->beginTransaction();

                try {
                    $this->saveTripPayment($tripPayment);

                    if (!$avoidPersistance) {
                        $this->entityManager->getConnection()->commit();
                    } else {
                        $this->entityManager->getConnection()->rollback();
                    }
                } catch (\Exception $e) {
                    $this->entityManager->getConnection()->rollback();
                    throw $e;
                }
            }
        }
    }

    /**
     * @param Trips $trip
     * @return TripPayments
     */
    public function retrieveTripCost(Trips $trip)
    {
        // retrieve the fare for the trip
        $fare = $this->faresService->getFare();

        // compute the payable minutes of the trip
        $tripMinutes = $this->cumulateMinutes($trip->getTripBills());

        // compute the minutes of parking
        $parkMinutes = $this->computeParkMinutes($trip, $tripMinutes);

        // retrieve the discount applied to the trip
        $discountPercentage = $trip->getDiscountPercentage();

        // compute the trip cost
        $cost = $this->tripFaresService->userTripCost($fare, $tripMinutes, $parkMinutes, $discountPercentage);

        return new TripPayments($trip, $fare, $tripMinutes, $parkMinutes, $discountPercentage, $cost);
    }

    /**
     * computes the total number of payable minutes of a trip, summing the
     * length of all the trip bills intervals
     *
     * @param PersistentCollection[TripBills] $tripBills
     * @return int
     */
    private function cumulateMinutes(PersistentCollection $tripBills)
    {
        $minutes = 0;

        foreach ($tripBills as $tripBill) {
            $minutes += $tripBill->getMinutes();
        }

        return $minutes;
    }

    /**
     * computes the minutes of parking of a trip
     *
     * @param Trips $trip
     * @param int $tripMintues
     * @return int
     */
    private function computeParkMinutes(Trips $trip, $tripMinutes)
    {
        // 29sec -> 0min, 30sec -> 1 min
        $tripParkMinutes = ceil(($trip->getParkSeconds() - 29) / 60);

        // we don't want to have more parking minutes than the payable length
        // of a trip
        return min($tripMinutes, $tripParkMinutes);
    }

    /**
     * persists the newly created tripPayment record
     *
     * @param TripPayments $tripPayment
     */
    private function saveTripPayment(TripPayments $tripPayment)
    {
        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();
    }
}

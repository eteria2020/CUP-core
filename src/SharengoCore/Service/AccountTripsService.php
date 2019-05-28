<?php

namespace SharengoCore\Service;

use SharengoCore\Service\BonusService;
use SharengoCore\Entity\Repository\CustomersBonusRepository;
use SharengoCore\Entity\Repository\FreeFaresRepository;
use SharengoCore\Utils\Interval;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripBills;
use SharengoCore\Entity\TripBonuses;
use SharengoCore\Entity\FreeFares;
use SharengoCore\Entity\TripFreeFares;
use SharengoCore\Entity\CustomersBonus as Bonus;

use Doctrine\ORM\EntityManager;

class AccountTripsService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var CustomersBonusRepository
     */
    private $bonusRepository;

    /**
     * @var FreeFaresRepository
     */
    private $freeFaresRepository;

    /**
     * @var BonusService
     */
    private $bonusService;

    /**
     * @var FreeFaresService
     */
    private $freeFaresService;

    /**
     * @var Trips
     */
    private $originalTrip;

    public function __construct(
        EntityManager $entityManager,
        CustomersBonusRepository $bonusRepository,
        FreeFaresRepository $freeFaresRepository,
        BonusService $bonusService,
        FreeFaresService $freeFaresService
    ) {
        $this->entityManager = $entityManager;
        $this->bonusRepository = $bonusRepository;
        $this->freeFaresRepository = $freeFaresRepository;
        $this->bonusService = $bonusService;
        $this->freeFaresService = $freeFaresService;
    }

    /**
     * THIS IS THE ONLY ENTRY POINT TO THIS CLASS
     *
     * flags a trip as accounted after performing all the necesasry operations:
     * - writes how the trip cost needs to be accounted between free fares, boununes, and invoices
     * - updates the bounuses according to how much they were used for the trip
     *
     * @param Trips $trip
     * @param boolean $avoidPersistance
     * @throws \Exception
     */
    public function accountTrip(Trips $trip, $avoidPersistance = false)
    {
        $this->originalTrip = $trip;

        $this->entityManager->getConnection()->beginTransaction();

        try {
            // divides the trip between free fares, bonuses and normal fares
            $tripBills = $this->processTripAccountingDetails(clone $trip);

            // flag the trip as accounted
            $trip->setIsAccounted(true);

            $this->entityManager->persist($trip);
            $this->entityManager->flush();

            if (!$avoidPersistance) {
                $this->entityManager->getConnection()->commit();
            } else {
                $this->entityManager->getConnection()->rollback();
            }

            return $tripBills;
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * saves how the trip cost is split between free fares, bonuses and normal fares
     *
     * @var Trips $trip
     * @return array associating to bonus ids the minutes that were consumed in the trip
     */
    private function processTripAccountingDetails(Trips $trip)
    {
        // first see if we can apply a free fare to the trip
        $trips = $this->applyFreeFares($trip);

        // then see if we can use some bonuses
        $bonuses = $this->bonusRepository->getBonusesForTrip($trip);

        //check if the customer has WomenVoucher to apply
        $womenBonuses = $this->bonusRepository->getWomenBonusesForTrip($trip);

        if(count($womenBonuses) > 0){
            $bonuses = array_merge($womenBonuses, $bonuses);
        }

        $trips = $this->applyBonuses($trips, $bonuses);

        // eventually consider billable part
        return $this->billTrips($trips);
    }

    /**
     * Removes from a trip the free fares periods and saves them in persistance layer
     *
     * @param Trips $trip
     * @return Trips[]
     */
    private function applyFreeFares(Trips $trip)
    {
        $freeFares = $this->freeFaresRepository->findAllActive();

        $trips = [$trip];

        foreach ($freeFares as $freeFare) {
            $trips = $this->applyFreeFare($trips, $freeFare);
        }

        return $trips;
    }

    /**
     * Removes a free fare from an array of trips
     * Returns an array of trips obtained by removing the free fare periods from
     * the trips
     *
     * @param Trips[] $trips
     * @param FreeFares $freeFare
     * @return Trips[]
     */
    private function applyFreeFare(array $trips, FreeFares $freeFare)
    {
        $newTrips = [];

        foreach ($trips as $trip) {
            $tripTrips = $this->applyFreeFareToTrip($trip, $freeFare);
            $newTrips = array_merge($newTrips, $tripTrips);
        }

        return $newTrips;
    }

    /**
     * Apply a free fare to a single trip
     * Returns an array of trips obtained by removing the bonus periods from
     * the trip
     *
     * @param Trips $trip
     * @param FreeFare $freeFare
     * @return Trips[]
     */
    private function applyFreeFareToTrip(Trips $trip, FreeFares $freeFare)
    {
        $intervals = $this->freeFaresService->usedInterval($trip, $freeFare);

        foreach ($intervals as $interval) {
            $freeFareIntervalTrip = $this->newTripFromInterval($trip, $interval);

            $tripFreeFare = TripFreeFares::createFromTripAndFreeFare($freeFareIntervalTrip, $freeFare);
            $tripFreeFare->setTrip($this->originalTrip);

            $this->entityManager->persist($tripFreeFare);
            $this->entityManager->flush();
        }

        return $this->removeIntervalsFromTrip($trip, $intervals);
    }

    /**
     * Apply a list of bonuses to the given set of trips
     *
     * @param Trips[] $trips
     * @param Bonus[] $bonuses
     * @return Trips[]
     */
    private function applyBonuses(array $trips, array $bonuses)
    {
        $newTrips = $trips;

        foreach ($bonuses as $bonus) {
            $newTrips = $this->applyBonus($newTrips, $bonus);
        }

        return $newTrips;
    }

    /**
     * Apply a bonus to a list of trips
     * Returns an array of trips obtained by removing the bonus periods from
     * the trips
     *
     * @param Trips[] $trips
     * @param Bonus $bonus
     *
     */
    private function applyBonus(array $trips, Bonus $bonus)
    {
        $newTrips = [];

        foreach ($trips as $trip) {

            list($bonus, $tripTrips) = $this->applyBonusToTrip($trip, $bonus);
            $newTrips = array_merge($newTrips, $tripTrips);
        }

        return $newTrips;
    }

    /**
     * Apply a single bonus to a single trip
     * Returns the modified bonus and an array of trips obtained by removing
     * the bonus periods from the trip
     *
     * @param Trips $trip
     * @param Bonus $bonus
     * @return [Bonus, Trips[]]
     */
    private function applyBonusToTrip(Trips $trip, Bonus $bonus)
    {
        $remains = [$trip];

        $interval = $this->bonusService->usedInterval($trip, $bonus);

        if ($interval && $bonus->getResidual() > 0) {
            $bonus = $this->bonusService->decreaseBonusMinutes($bonus, $interval->minutes());

            $bonusIntervalTrip = $this->newTripFromInterval($trip, $interval);

            $tripBonus = TripBonuses::createFromTripAndBonus($bonusIntervalTrip, $bonus);
            $tripBonus->setTrip($this->originalTrip);

            $this->entityManager->persist($tripBonus);
            $this->entityManager->flush();

            $remains = $this->removeIntervalFromTrip($trip, $interval);
        }

        return [$bonus, $remains];
    }

    /**
     * Returns a new trip modifying only the timestamps according to the interval
     *
     * @param Trips $trip
     * @param Interval $interval
     * @return Trips
     */
    private function newTripFromInterval(Trips $trip, Interval $interval)
    {
        $newTrip = clone $trip;

        $newTrip->setTimestampBeginning($interval->start())
            ->setTimestampEnd($interval->end());

        return $newTrip;
    }

    /**
     * removes an array of intervals from a single trip. Returns what it remains
     *
     * @param Trips $trip
     * @param Interval[] $intervals
     * @return Trips[]
     */
    private function removeIntervalsFromTrip(Trips $trip, array $intervals)
    {
        $trips = [$trip];

        foreach ($intervals as $interval) {
            $trips = $this->removeIntervalFromTrips($trips, $interval);
        }

        return $trips;
    }

    /**
     * removes an interval from an array of trips. Returns what it remains
     *
     * @param Trips[] $trips
     * @param Interval $interval
     * @return Trips[]
     */
    private function removeIntervalFromTrips(array $trips, Interval $interval)
    {
        $newTrips = [];

        foreach ($trips as $trip) {
            $tripTrips = $this->removeIntervalFromTrip($trip, $interval);
            $newTrips = array_merge($newTrips, $tripTrips);
        }

        return $newTrips;
    }

    /**
     * removes an interval from a single trip. Returns what it remains
     *
     * @param Trips $trip
     * @param Interval $interval
     * @return Trips[]
     */
    private function removeIntervalFromTrip(Trips $trip, Interval $interval)
    {
        $tripInterval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        if ($tripInterval->strictlyContains($interval->start())) {
            if ($tripInterval->strictlyContains($interval->end())) {
                // the bonus starts and ends during the trip
                $firstTrip = clone $trip;
                $secondTrip = clone $trip;

                // end 1 second before interval start
                $firstTrip->setTimestampEnd($interval->start()->sub(new \DateInterval('PT1S')));

                // start 1 second after interval end
                $secondTrip->setTimestampBeginning($interval->end()->add(new \DateInterval('PT1S')));

                return [$firstTrip, $secondTrip];
            } else {
                // the bonus starts during the trips and ends later
                $newTrip = clone $trip;

                // end 1 second before interval start
                $newTrip->setTimestampEnd($interval->start()->sub(new \DateInterval('PT1S')));

                return [$newTrip];
            }
        } elseif ($tripInterval->strictlyContains($interval->end())) {
            // the bonus starts before the trip and ends during the trip
            $newTrip = clone $trip;

            // start 1 second after interval end
            $newTrip->setTimestampBeginning($interval->end()->add(new \DateInterval('PT1S')));

            return [$newTrip];
        } elseif ($interval->contains($tripInterval->start()) && $interval->contains($tripInterval->end())) {
            // the bonus starts before the trip and ends later
            return [];
        }

        return [$trip];
    }

    /**
     * @param Trips[] $trips
     * @return TripBills[]
     */
    private function billTrips(array $trips)
    {
        $tripBills = [];

        foreach ($trips as $trip) {
            $tripBills[] = $this->billTrip($trip);
        }

        return $tripBills;
    }

    /**
     * Bills a trip saving it in persistance layer
     *
     * @param Trips $trip
     * @return TripBills
     */
    private function billTrip(Trips $trip)
    {
        $billTrip = TripBills::createFromTrip($trip);
        $billTrip->setTrip($this->originalTrip);

        $this->entityManager->persist($billTrip);
        $this->entityManager->flush();

        return $billTrip;
    }
}

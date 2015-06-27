<?php

namespace SharengoCore\Service;

use SharengoCore\Service\BonusService;
use SharengoCore\Entity\Repository\CustomersBonusRepository;
use SharengoCore\Utils\Interval;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripBills;
use SharengoCore\Entity\TripBonuses;
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
     * @var BonusService
     */
    private $bonusService;

    /**
     * @var Trips
     */
    private $originalTrip;

    public function __construct(
        EntityManager $entityManager,
        CustomersBonusRepository $bonusRepository,
        BonusService $bonusService
    ) {
        $this->entityManager = $entityManager;
        $this->bonusRepository = $bonusRepository;
        $this->bonusService = $bonusService;
    }

    /**
     * THIS IS THE ONLY ENTRY POINT TO THIS CLASS
     *
     * flags a trip as accounted after performing all the necesasry operations:
     * - writes how the trip cost needs to be accounted between free fares, boununes, and invoices
     * - updates the bounuses according to how much they were used for the trip
     *
     * @param Trips $trip
     * @throws \Exception
     */
    public function accountTrip(Trips $trip)
    {
        $this->originalTrip = $trip;

        $this->entityManager->getConnection()->beginTransaction();

        try {
            // divides the trip between free fares, bonuses and normal fares
            $this->processTripAccountingDetails(clone $trip);

            // flag the trip as accounted
            $trip->setIsAccounted(true);

            $this->entityManager->persist($trip);
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
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
        //first see if we can apply a free fare to the trip
        $trips = $this->applyFreeFares($trip);

        // then see if we can use some bonuses
        $bonuses = $this->bonusRepository->getBonusesForTrip($trip);
        $trips = $this->applyBonuses($trips, $bonuses);

        /*foreach($trips as $trip) {
            echo $trip->getTimestampBeginning()->format('Y-m-d H:i:s') . '-' . $trip->getTimestampEnd()->format('Y-m-d H:i:s') . "\n";
        }*/
        
        // then see if we can use some bonuses
        /*$billableTrips = [];

        foreach ($trips as $trip) {
            $billableTrips = array_merge($billableTrips, $this->applyBonuses($trip));
        }*/

        // eventually consider billable part
        $this->billTrips($trips);
    }

    /**
     * Removes from a trip the free fares periods and saves them in persistance layer
     *
     * @param Trips $trip
     * @return Trips[]
     */
    private function applyFreeFares(Trips $trip)
    {
        return [$trip]; // TODO: fix this to consider free fares
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
    public function applyBonus(array $trips, Bonus $bonus)
    {
        $newTrips = [];

        foreach ($trips as $trip) {
            list($bonus, $tripTrips) = $this->applyBonusToTrip($trip, $bonus);
            $newTrips = array_merge($newTrips, $tripTrips);
        }

        return $newTrips;
    }

    /**
     * Apply a single bonus to a signle trip
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

        if ($interval) {
            $intervals[] = $interval;

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
     * remove an interval from a single trip. Returns what it remains
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
        } else if ($tripInterval->strictlyContains($interval->end())) {
            // the bonus starts before the trip and ends during the trip
            $newTrip = clone $trip;

            // start 1 second after interval end
            $newTrip->setTimestampBeginning($interval->end()->add(new \DateInterval('PT1S')));

            return [$newTrip];
        } else if ($interval->contains($tripInterval->start()) && $interval->contains($tripInterval->end())) {
            // the bonus starts before the trip and ends later
            return [];
        }

        return [$trip];
    }

    private function billtrips(array $trips)
    {
        foreach ($trips as $trip) {
            $this->billTrip($trip);
        }
    }

    /**
     * Bills a trip saving it in persistance layer
     *
     * @param Trips $trip
     */
    private function billTrip(Trips $trip)
    {
        list($price, $vat) = $this->computeCost($trip);
        $trip->setPriceCent($price);
        $trip->setVatCent($vat);

        $billTrip = TripBills::createFromTrip($trip);
        $billTrip->setTrip($this->originalTrip);

        $this->entityManager->persist($billTrip);
        $this->entityManager->flush();
    }

    /**
     * computes the cost of a trip
     *
     * @param Trips $trip
     * @retun array
     */
    private function computeCost(Trips $trip)
    {
        return [0, 0]; //TODO: fix this
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Repository\BonusRepository;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripBills;
use SharengoCore\Entity\TripBonuses;
use SharengoCore\Entity\Interval;
use SharengoCore\Service\BonusService;

use Doctrine\ORM\EntityManager;
use Zend\View\Helper\Url;

class TripsService
{
    const DURATION_NOT_AVAILABLE = 'n.d.';

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /** @var TripsRepository */
    private $tripRepository;

    /**
     * @var BonusRepository
     */
    private $bonusRepository;

    /**
     * @var BonusService
     */
    private $bonusService;

    /**
     * @var DatatableServiceInterface
     */
    private $I_datatableService;

    /** @var */
    private $I_urlHelper;

    /**
     * @var array Bonuses
     */
    private $bonuses;

    public function __construct(
        EntityManager $entityManager,
        TripsRepository $tripRepository,
        BonusRepository $bonusRepository,
        BonusService $bonusService,
        DatatableService $I_datatableService,
        $I_urlHelper
    ) {
        $this->entityManager = $entityManager;
        $this->tripRepository = $tripRepository;
        $this->bonusRepository = $bonusRepository;
        $this->bonusService = $bonusService;
        $this->I_datatableService = $I_datatableService;
        $this->I_urlHelper = $I_urlHelper;
    }

    /**
     * @return mixed
     */
    public function getTripsByCustomer($customerId)
    {
        return $this->tripRepository->findTripsByCustomer($customerId);
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $trips = $this->I_datatableService->getData('Trips', $as_filters);

        return array_map(function (Trips $trip) {

            $urlHelper = $this->I_urlHelper;
            $plate = sprintf(
                '<a href="%s">%s</a>',
                $urlHelper(
                    'cars/edit',
                    ['plate' => $trip->getCar()->getPlate()]
                ),
                $trip->getCar()->getPlate()
            );

            return [
                'e'        => [
                    'id'                 => $trip->getId(),
                    'kmBeginning'        => $trip->getKmBeginning(),
                    'kmEnd'              => $trip->getKmEnd(),
                    'timestampBeginning' => $trip->getTimestampBeginning()->format('d-m-Y H:i:s'),
                    'timestampEnd'       => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('d-m-Y H:i:s') : ''),
                    'parkSeconds'        => $trip->getParkSeconds() . ' sec',
                    'payable'            => $trip->getPayable() ? 'Si' : 'No',
                ],
                'cu'       => [
                    'surname' => $trip->getCustomer()->getSurname(),
                    'name'    => $trip->getCustomer()->getName(),
                    'mobile'  => $trip->getCustomer()->getMobile(),
                ],
                'c'        => [
                    'plate'     => $plate,
                    'label'     => $trip->getCar()->getLabel(),
                    'parking'   => $trip->getCar()->getParking() ? 'Si' : 'No',
                    'keyStatus' => $trip->getCar()->getKeystatus()
                ],
                'cc'       => [
                    'code' => is_object($trip->getCustomer()->getCard()) ? $trip->getCustomer()->getCard()->getCode() : '',

                ],
                'duration' => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd()),
                'price'    => ($trip->getPriceCent() + $trip->getVatCent()),

            ];
        }, $trips);
    }

    public function getTotalTrips()
    {
        return $this->tripRepository->getTotalTrips();
    }

    public function getDuration($s_from, $s_to)
    {
        if ('' != $s_from && '' != $s_to) {

            $date = $s_from->diff($s_to);

            $days = (int)$date->format('%d');

            if ($days > 0) {
                return sprintf('%sg %s:%s:%s', $days, $date->format('%H'), $date->format('%I'), $date->format('%S'));
            } else {
                return sprintf('0g %s:%s:%s', $date->format('%H'), $date->format('%I'), $date->format('%S'));
            }

        }

        return self::DURATION_NOT_AVAILABLE;
    }

    public function getUrlHelper()
    {
        return $this->I_viewHelperManager->get('url');
    }

    /**
     * flags a trip as accounted after performing all the necesasry operations:
     * - writes how the trip cost needs to be accounted between free fares, boununes, and invoices
     * - updates the bounuses according to how much they were used for the trip
     *
     * @param Trips $trip
     * @throws \Exception
     */
    public function accountTrip(Trips $trip)
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            // divides the trip between free fares, bonuses and normal fares
            $this->processTripAccountingDetails($trip);

            // flag a trip as accounted
            $this->trip->setIsAccounted(true);

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
        $billableTrips = [];
        $bonusesMinutes = [];

        foreach ($trips as $trip) {
            $billableTrips = array_merge($billableTrips, $this->applyBonuses($trip));
        }

        // eventually consider billable part
        foreach ($billableTrips as $trip) {
            $this->billTrip($trip);
        }

        return $bonusesMinutes;
    }

    /**
     * Removes from a trip the free fares periods and saves them in persistance layer
     *
     * @param Trips $trip
     * @return Trips[]
     */
    public function applyFreeFares(Trips $trip)
    {
        return [$trip];
    }

    /**
     * Removes from a trip the bonuses, saves them in persistance layer
     * and stores them in the class bonus property
     *
     * @param Trips $trip
     * @return Trips[]
     */
    public function applyBonuses(Trips $trip)
    {
        $bonuses = $this->bonusRepository->getBonusesForTrip($trip);

        $intervals = [];

        //update the bonuses and save the trip bonuses
        foreach ($bonuses as $bonus) {
            $interval = $this->bonusService->usedInterval($trip, $bonus);
            if ($interval) {
                $intervals[] = $interval;

                $this->bonusService->decreaseBonusMinutes($bonus, $interval->minutes());

                $bonusIntervalTrip = $this->newTripFromInterval($trip, $interval);

                $tripBonus = TripBonuses::createFromTrip($bonusIntervalTrip);

                $this->entityManager->persist($tripBonus);
                $this->entityManager->flush();
            }
        }

        return $this->removeIntervalFromTrip($trip, $intervals);
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
     * Returns an array of trips resulting from the substraction of the
     * intervals from the original trip
     *
     * @param Trios $trip
     * @param Intervals[] $intervals
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
     * remove an interval of time from a set of trips
     *
     * @param Trips[] $trips
     * @param Interval $interval
     * @return Trips[]
     */
    private function removeIntervalFromTrips(array $trips, Interval $interval)
    {
        $newTrips = [];

        foreach ($trips as $trip) {
            $newTrips = array_merge($newTrips, $this->removeIntervalFromTrip($trip, $interval));
        }

        return $newTrips;
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

        if ($tripInterval->contains($interva->start())) {
            if ($tripInterval->contains($interval->end())) {
                // the bonus starts and ends during the trip
                $firstTrip = clone $trip;
                $secondTrip = clone $trip;

                $firstTrip->setTimestampEnd($interval->start());
                $secondTrip->setTimestampBeginning($interval->end());

                return [$firstTrip, $secondTrip];
            } else {
                // the bonus starts during the trips and ends later
                $newTrip = clone $trip;

                $newTrip->setTimestampEnd($interval->end());

                return [$newTrip];
            }
        } else if ($tripInterval->contains($interval->end())) {
            // the bonus starts before the trip and ends during the trip
            $newTrip = clone $trip;

            $newTrip->setTimestampBeginning($interval->start());

            return [$newTrip];
        } else if ($interval->contains($tripInterval->start()) && $interval->contains($tripInterval->end())) {
            // the bonus starts before the trip and ends later
            return [];
        }

        return [$trip];
    }

    /**
     * Bills a trip saving it in persistance layer
     *
     * @param Trips $trip
     */
    public function billTrip(Trips $trip)
    {
        list($price, $vat) = $this->computeCost($trip);
        $trip->setPriceCent($price);
        $trip->setVatCent($vat);

        $billTrip = TripBills::createFromTrip($trip);

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
        //TODO
    }
}

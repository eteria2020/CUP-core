<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Repository\ReservationsRepository;
use SharengoCore\Entity\ReservationsArchive;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\FreeFares;
use SharengoCore\Entity\Customers;
use SharengoCore\Utils\Interval;
use SharengoCore\Service\EventsService;

class FreeFaresService {

    private $entityManager;

    /**
     * @var TripsRepository
     */
    private $tripsRepository;

    /**
     * @var ReservationsRepository
     */
    private $reservationsRepository;

    /**
     *
     * @var EventsService
     */
    private $eventsService;

    public function __construct(
    TripsRepository $tripsRepository, ReservationsREpository $reservationsRepository, EntityManager $entityManager, EventsService $eventsService) {
        $this->tripsRepository = $tripsRepository;
        $this->reservationsRepository = $reservationsRepository;
        $this->entityManager = $entityManager;
        $this->eventsService = $eventsService;
    }

    /**
     * Returns the intervals given by the intersection between the trip and the
     * free fare conditions
     *
     * @param Trips $trip
     * @param FreeFares $freeFare
     * @return Intervals[]
     */
    public function usedInterval(Trips $trip, FreeFares $freeFare) {
        $conditions = json_decode($freeFare->getConditions(), true);

        $tripInterval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        $intervals = [$tripInterval];

        if (isset($conditions['car'])) {
            if (isset($conditions['car']['type'])) {
                if ($conditions['car']['type']==='unplug') {
                     $intervals = $this->filterPlugUnPlug($intervals, false, $trip, $conditions['car']);
                }
            } else {
                $intervals = $this->filterCar($intervals, $trip, $conditions['car']);
            }
        } else if (isset($conditions['customer'])) {
            $intervals = $this->filterCustomer($intervals, $trip->getCustomer(), $conditions['customer']);
        } else if (isset($conditions['time'])) {
            $intervals = $this->filterTime($intervals, $conditions['time']);
        } else {
            $intervals = [];
        }

        return $intervals;
    }

    /**
     * Filters out the intervals not satisfying the freeFare conditions on the
     * customer
     *
     * @param Intervals[] $intervals
     * @param Customers $customer
     * @param array $customerConditions
     * @return Intervals[]
     */
    private function filterCustomer(array $intervals, Customers $customer, array $customerConditions) {
        if (isset($customerConditions['gender'])) {
            $intervals = $this->filterCustomerGender($intervals, $customer->getGender(), $customerConditions['gender']);
        }

        if (isset($customerConditions['birth_date'])) {
            if ($customer->getBirthdate()) {
                $intervals = $this->filterCustomerBirthday($intervals, $customer->getBirthdate());
            } else {
                $intervals = [];
            }
        }

        return $intervals;
    }

    /**
     * @param Intervals[] $intervals
     * @param string $gender
     * @param string $genderCondition
     * @return Intervals[]
     */
    private function filterCustomerGender(array $intervals, $gender, $genderCondition) {
        if ($gender != $genderCondition) {
            return [];
        }

        return $intervals;
    }

/**
 *
 * @param array $intervals
 * @param boolean $flagPlug
 * @param Trips $trip
 * @param array $conditions
 * @return type
 */
    private function filterPlugUnPlug(array $intervals, $flagPlug, Trips $trip, array $conditions) {
        $result = [];

        if(isset($conditions['value'])){
            if($conditions['value']>0){
                if (self::verifyFilterPlugUnPlug($flagPlug, $trip, $this->eventsService)) {
                    $start = $trip->getTimestampBeginning();
                    $end = clone $start;
                    $end->modify('+' . $conditions['value'] . ' minutes');
                    $plugInterval = new Interval($start, $end);

                    foreach ($intervals as $interval) {
                        $intersection = $interval->intersection($plugInterval);

                        if ($intersection) {
                            $result[] = $intersection;
                        }
                    }
                }
            }
        }

        return $result;
    }


    /**
     * @param Intervals[] $intervals
     * @param DateTime $birthdate
     * @return Intervals[]
     */
    private function filterCustomerBirthday(array $intervals, \Datetime $birthdate) {
        $birthday = $birthdate->format('m-d');

        $newIntervals = [];

        foreach ($intervals as $interval) {
            foreach ($interval->years() as $year) {
                $birthdayStart = date_create_from_format('Y-m-d H:i:s', $year . '-' . $birthday . ' 00:00:00');
                $birthdayEnd = date_create_from_format('Y-m-d H:i:s', $year . '-' . $birthday . ' 23:59:59');
                $birthdayInterval = new Interval($birthdayStart, $birthdayEnd);

                $intersection = $interval->intersection($birthdayInterval);

                if ($intersection) {
                    $newIntervals[] = $intersection;
                }
            }
        }

        return $newIntervals;
    }

    /**
     * @param Intervals[] $intervals
     * @param array $timeConditions has keys `from` and `to`
     * @return Intervals[]
     */
    private function filterTime(array $intervals, array $timeConditions) {
        $newIntervals = [];

        foreach ($intervals as $interval) {
            foreach ($interval->days() as $day) {
                $start = clone $day;
                $end = clone $day;
                $start->modify($timeConditions['from']);
                $end->modify($timeConditions['to']);

                $filterInterval = new Interval($start, $end);

                $intersection = $interval->intersection($filterInterval);

                if ($intersection) {
                    $newIntervals[] = $intersection;
                }
            }
        }

        return $newIntervals;
    }

    /**
     * @param Intervals[] $intervals
     * @param array $carConditions has keys type, hour  minutes
     * @param Trips $trip
     * @return Intervals[]
     */
    private function filterCar(array $intervals, Trips $trip, array $carConditions) {
        $newIntervals = [];
        if ($carConditions['type'] == 'nouse') {

            if (self::verifyFilterCar($trip, $carConditions, $this->tripsRepository, $this->reservationsRepository)) {

                $start = $trip->getTimestampBeginning();
                $end = clone $start;
                $end->modify('+' . $carConditions['value'] . ' minutes');
                $carInterval = new Interval($start, $end);

                foreach ($intervals as $interval) {
                    $intersection = $interval->intersection($carInterval);

                    if ($intersection) {
                        $newIntervals[] = $intersection;
                    }
                }
            }
        }
        return $newIntervals;
    }

    static function verifyFilterCar(Trips $trip, array $carConditions, TripsRepository $tripsRepository, ReservationsRepository $reservationsRepository) {

        $reservation = $reservationsRepository->findReservationByTrip($trip);

        if (!is_null($reservation) && $reservation instanceof ReservationsArchive) {
            $date = $reservation->getBeginningTs();
        } else {
            $date = $trip->getTimestampBeginning();
        }

        if (!isset($carConditions['dow'][$date->format('w')])) { //check the day of the week
            return false;
        }

        $time = explode("-", $carConditions['dow'][$date->format('w')]); // retrieve the time interval
        $start = new \DateTime($date->format('Y-m-d') . ' ' . $time[0]);
        $end = new \DateTime($date->format('Y-m-d') . ' ' . $time[1]);

        if ($date >= $start && $date <= $end) {

            if ($trip->getFleet()->getId() != $carConditions['fleet']) {
                return false;
            }

            $check = $tripsRepository->findPreviousTrip($trip);

            if (is_null($check)) {
                return false;
            }

            if (is_null($check->getTimestampEnd())) {
                return false;
            }

            $minutes = $carConditions['hour'] * 60;

            if (isset($carConditions['max'])) {
                $maxMinutes = ($carConditions['max'] * 60);
            } else {
                $maxMinutes = NULL;
            }

            $tripsInterval = $trip->getTimestampBeginning()->diff($check->getTimestampEnd(), true);
            $checkMinutes = ($tripsInterval->days * 24 * 60) + ($tripsInterval->h * 60) + $tripsInterval->i;

            if (($trip->getBatteryBeginning() > $carConditions['soc']) && ($checkMinutes >= $minutes && ($maxMinutes == NULL || $checkMinutes < $maxMinutes))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     *
     * @param bool $flagPlug
     * @param Trips $trip
     * @param EventsRepository $eventsService
     * @return boolean
     */
    static function verifyFilterPlugUnPlug($flagPlug, Trips $trip, EventsService $eventsService) {
        $result = false;
        try {

            if(!$flagPlug) {    // unplug condition
                $events = $eventsService->getEventsByTrip($trip);
                $eventLastMaintenance = null;
                foreach ($events as $event) {
                    if ($event->getEventId() == 21) {            // event MAINTENANCE
                        $eventLastMaintenance = $event;
                    }
                }

                if (!is_null($eventLastMaintenance)) {
                    if ($eventLastMaintenance->getTxtval() === "EndCharging") {
                        $result = true;
                    }
                }
            }
        } catch (Exception $ex) {

        }

        return $result;
    }

}

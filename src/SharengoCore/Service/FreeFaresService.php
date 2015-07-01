<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\FreeFares;
use SharengoCore\Entity\Customers;
use SharengoCore\Utils\Interval;

class FreeFaresService
{
    /**
     * Returns the intervals given by the intersection between the trip and the
     * free fare conditions
     *
     * @param Trips $trip
     * @param FreeFares $freeFare
     * @return Intervals[]
     */
    public function usedInterval(Trips $trip, FreeFares $freeFare)
    {
        $conditions = json_decode($freeFare->getConditions(), true);

        $tripInterval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        $intervals = [$tripInterval];

        if (isset($conditions['customer'])) {
            $intervals = $this->filterCustomer($intervals, $trip->getCustomer(), $conditions['customer']);
        }

        if (isset($conditions['time'])) {
            $intervals = $this->filterTime($intervals, $conditions['time']);
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
    private function filterCustomer(array $intervals, Customers $customer, array $customerConditions)
    {
        if (isset($customerConditions['gender'])) {
            $intervals = $this->filterCustomerGender($intervals, $customer->getGender(), $customerConditions['gender']);
        }

        if (isset($customerConditions['birth_date'])) {
            $intervals = $this->filterCustomerBirthday($intervals, $customer->getBirthdate());
        }

        return $intervals;
    }

    /**
     * @param Intervals[] $intervals
     * @param string $gender
     * @param string $genderCondition
     * @return Intervals[]
     */
    private function filterCustomerGender(array $intervals, $gender, $genderCondition)
    {
        if ($gender != $genderCondition) {
            return [];
        }

        return $intervals;
    }

    /**
     * @param Intervals[] $intervals
     * @param DateTime $birthdate
     * @return Intervals[]
     */
    private function filterCustomerBirthday(array $intervals, \Datetime $birthdate)
    {
        $birthday = $birthdate->format('m-d');

        $newIntervals = [];

        foreach ($intervals as $interval) {
            foreach ($interval->years() as $year) {
                $birthdayStart = date_create_from_format('Y-m-d H:i:s', $year.'-'.$birthday.' 00:00:00');
                $birthdayEnd = date_create_from_format('Y-m-d H:i:s', $year.'-'.$birthday.' 23:59:59');
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
    private function filterTime(array $intervals, array $timeConditions)
    {
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
}

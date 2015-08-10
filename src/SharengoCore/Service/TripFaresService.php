<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Fares;

class TripFaresService
{
    /**
     * computes the cost of a trip of $minutes minutes according to the fare
     *
     * @param Fares $fare
     * @param int $minutes
     * @return int cost of the trip in euros
     */
    private function minutesToEuros(Fares $fare, $minutes)
    {
        $previousStep = INF;

        foreach ($fare->getCostSteps() as $step => $stepCost) {
            if ($minutes > $step) {
                return min($previousStep, $stepCost + $this->minutesToEuros($fare, $minutes - $step));
            }

            $previousStep = $stepCost;
        }

        return min($previousStep, $fare->getMotionCostPerMinute() * $minutes);
    }

    /**
     * computes the cost of a trip considering the minutes of parking
     *
     * @param Fares $fare
     * @param int $tripMinutes includes the parking minutes
     * @param int $parkMinutes
     */
    private function tripCost(Fares $fare, $tripMinutes, $parkMinutes)
    {
        return min(
            $this->minutesToEuros($fare, $tripMinutes),
            $this->minutesToEuros($fare, $tripMinutes - $parkMinutes) + $parkMinutes * $fare->getParkCostPerMinute()
        );
    }

    /**
     * computes the cost of a trip considering the percentage of discount for a
     * given user
     *
     * @param Fares $fare
     * @param int $tripMinutes includes the parking minutes
     * @param int $parkMinutes
     * @param int $discountPercentage
     */
    public function userTripCost(Fares $fare, $tripMinutes, $parkMinutes, $discountPercentage)
    {
        return $this->tripCost($fare, $tripMinutes, $parkMinutes) * (100 - $discountPercentage) / 100;
    }
}

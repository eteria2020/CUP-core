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
     * @param int $discountPercentage
     * @return int cost of the trip in euros
     */
    private function minutesToEuros(Fares $fare, $minutes, $discountPercentage = 0)
    {
        $previousStep = INF;

        foreach ($fare->getCostSteps() as $step => $stepCost) {
            if ($minutes > $step) {
                return min($previousStep, $stepCost + $this->minutesToEuros($fare, $minutes - $step, $discountPercentage));
            }

            $previousStep = $stepCost;
        }

        return min($previousStep, round($fare->getMotionCostPerMinute() * $minutes * (100 - $discountPercentage) / 100));
    }

    /**
     * computes the cost of a trip considering the minutes of parking, 
     * but apply the discont only trip minutes (no parking)
     *
     * @param Fares $fare
     * @param int $tripMinutes includes the parking minutes
     * @param int $parkMinutes
     */
    private function tripCostNoParkingDiscount(Fares $fare, $tripMinutes, $parkMinutes, $discountPercentage)
    {
        $discount =(100 - $discountPercentage) / 100;
        
        return min(
            $this->minutesToEuros($fare, $tripMinutes) * $discount,
            ($this->minutesToEuros($fare, $tripMinutes - $parkMinutes) * $discount) + ($parkMinutes * $fare->getParkCostPerMinute())
        );
    }

    /**
     * computes the cost of a trip considering the minutes of parking
     *
     * @param Fares $fare
     * @param int $tripMinutes includes the parking minutes
     * @param int $parkMinutes
     * @parma int $discountPercentage
     * @return int
     */
    private function tripCost(Fares $fare, $tripMinutes, $parkMinutes, $discountPercentage = 0)
    {
        return min(
            $this->minutesToEuros($fare, $tripMinutes, $discountPercentage),
            $this->minutesToEuros($fare, $tripMinutes - $parkMinutes, $discountPercentage) + $parkMinutes * $fare->getParkCostPerMinute()
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
        return $this->tripCost($fare, $tripMinutes, $parkMinutes, $discountPercentage);
    }
}

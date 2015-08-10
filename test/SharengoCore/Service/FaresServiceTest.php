<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Fares;

class TripFaresServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fares
     */
    private $fare;

    /**
     * @var TripFaresService
     */
    private $tripFaresService;

    public function setUp()
    {
        $this->tripFaresService = new TripFaresService();
        $this->fare = $this->getFare();
    }

    private function getFare()
    {
        $fare = new Fares();

        $reflectionClass = new \ReflectionClass('SharengoCore\Entity\Fares');

        $motionCostPerMinuteProperty = $reflectionClass->getProperty('motionCostPerMinute');
        $motionCostPerMinuteProperty->setAccessible(true);
        $motionCostPerMinuteProperty->setvalue($fare, 28);

        $parkCostPerMinuteProperty = $reflectionClass->getProperty('parkCostPerMinute');
        $parkCostPerMinuteProperty->setAccessible(true);
        $parkCostPerMinuteProperty->setvalue($fare, 10);

        $costSteps = [
            1440 => 5000,
            240 => 3000,
            60 => 1200
        ];

        $costStepsProperty = $reflectionClass->getProperty('costSteps');
        $costStepsProperty->setAccessible(true);
        $costStepsProperty->setvalue($fare, json_encode($costSteps));

        return $fare;
    }

    public function tripsProvider()
    {
        return [
            [20, 560],
            [43, 1200],
            [60, 1200],
            [61, 1228],
            [102, 2376],
            [103, 2400],
            [120, 2400],
            [121, 2428],
            [141, 2988],
            [142, 3000],
            [240, 3000],
            [241, 3028],
            [282, 4176],
            [283, 4200],
            [300, 4200],
            [301, 4228],
            [328, 4984],
            [329, 5000],
            [1440, 5000]
        ];
    }

    /**
     * @dataProvider tripsProvider
     *
     * @var minutes to be accounted
     * @var the expected cost in eurocents
     */
    public function testMinutesToEuros($minutes, $cost)
    {
        $minutesToEurosMethod = new \ReflectionMethod('SharengoCore\Service\TripFaresService', 'minutesToEuros');
        $minutesToEurosMethod->setAccessible(true);

        $this->assertEquals($cost, $minutesToEurosMethod->invoke($this->tripFaresService, $this->fare, $minutes));
    }

    public function tripsCostProvider()
    {
        return [
            [20, 0, 560],
            [50, 0, 1200],
            [55, 10, 1200],
            [70, 10, 1300],
            [150, 0, 3000],
            [330, 0, 5000],
            [1440, 0, 5000]
        ];
    }

    /**
     * @dataProvider tripsCostProvider
     */
    public function testTripCost($tripMinutes, $parkMinutes, $cost)
    {
        $tripCostMethod = new \ReflectionMethod('SharengoCore\Service\TripFaresService', 'tripCost');
        $tripCostMethod->setAccessible(true);

        $this->assertEquals(
            $cost,
            $tripCostMethod->invoke($this->tripFaresService, $this->fare, $tripMinutes, $parkMinutes)
        );
    }

    public function userTripsCostProvider()
    {
        return [
            [20, 0, 0, 560],
            [20, 0, 10, 560 * 9 / 10],
            [55, 0, 0, 1200],
            [55, 0, 30, 1200 * 7 / 10],
            [55, 10, 0, 1200],
            [55, 10, 30, 1200 * 7 / 10]
        ];
    }

    /**
     * @dataProvider userTripsCostProvider
     */
    public function testUserTripCost($tripMinutes, $parkMinutes, $discountPercentage, $cost)
    {
        $this->assertEquals(
            $cost,
            $this->tripFaresService->userTripCost($this->fare, $tripMinutes, $parkMinutes, $discountPercentage)
        );
    }
}

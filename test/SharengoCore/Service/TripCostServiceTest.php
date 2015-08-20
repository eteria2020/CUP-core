<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use Cartasi\Entity\Transactions;

class TripCostServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TripCostService
     */
    private $tripCostService;

    public function setUp()
    {
        $this->faresService = \Mockery::mock('SharengoCore\Service\FaresService');
        $this->tripFaresService = \Mockery::mock('SharengoCore\Service\TripFaresService');
        $this->entityManager = \Mockery::mock('Doctrine\ORM\EntityManager');

        $this->tripCostService = new TripCostService(
            $this->faresService,
            $this->tripFaresService,
            $this->entityManager
        );
    }

    public function parkMinutesProvider()
    {
        return [
            [0, 10, 0],
            [29, 10, 0],
            [30, 10, 1],
            [89, 10, 1],
            [120, 1, 1]
        ];
    }

    /**
     * @dataProvider parkMinutesProvider
     *
     * @param int seconds of parking
     * @param accountable trip minutes
     * @param resulting parking minutes
     */
    public function testComputeParkMinutes($parkSeconds, $tripMinutes, $parkMinutes)
    {
        $computeMinutesMethod = new \ReflectionMethod('SharengoCore\Service\TripCostService', 'computeParkMinutes');
        $computeMinutesMethod->setAccessible(true);

        $trip = (new Trips())->setParkSeconds($parkSeconds);

        $this->assertEquals(
            $parkMinutes,
            $computeMinutesMethod->invoke($this->tripCostService, $trip, $tripMinutes)
        );
    }
}

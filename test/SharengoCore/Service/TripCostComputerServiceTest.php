<?php

namespace SharengoCore\Service;

use SharengoCore\Bootstrap;
use SharengoCore\Entity\Fares;

class TripCostComputerServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TripCostComputerService
     */
    private $tripCostComputerService;

    /**
     * @var ServiceManager
     */
    private $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();

        $entityManager = \Mockery::mock('Doctrine\ORM\EntityManager');
        $faresRepository = \Mockery::mock('SharengoCore\Entity\Repository\FaresRepository');

        $costSteps = [
            1440 => 5000,
            240 => 3000,
            60 => 1200
        ];
        $fare = new Fares(28, 10, json_encode($costSteps));

        $faresRepository->shouldReceive('findOne')->andReturn($fare);
        $entityManager->shouldReceive('getRepository')->with('\SharengoCore\Entity\Fares')->andReturn($faresRepository);
        if (!$this->serviceManager->has('doctrine.entitymanager.orm_default')) {
            $this->serviceManager->setService('doctrine.entitymanager.orm_default', $entityManager);
        }

        $url = \Mockery::mock('Zend\View\Helper\Url');
        $this->serviceManager->get('ViewHelperManager')->setService('Url', $url);

        $this->tripCostComputerService = $this->serviceManager->get('SharengoCore\Service\TripCostComputerService');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function computeCostProvider()
    {
        return [
            // a trip with zero seconds cost 0 cents
            [
                date_create(),
                date_create(),
                0,
                'male',
                0,
                0
            ],
            // 20 minutes trip, without parking or bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('PT20M')),
                0,
                'male',
                0,
                560
            ],
            // 50 minutes trip, without parking or bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('PT50M')),
                0,
                'male',
                0,
                1200
            ],
            // 55 minutes trip, with 10 minutes of parking and no bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('PT55M')),
                600,
                'male',
                0,
                1200
            ],
            // 1 hour 10 minutes trip, with 10 minutes of parking and no bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('PT1H10M')),
                600,
                'male',
                0,
                1300
            ],
            // 2 hours 30 minutes trip, without parking or bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('PT2H30M')),
                0,
                'male',
                0,
                3000
            ],
            // 5 hours 30 minutes trip, without parking or bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('PT5H30M')),
                0,
                'male',
                0,
                5000
            ],
            // 24 hours trip, without parking or bonuses
            [
                date_create(),
                date_create()->add(new \DateInterval('P1D')),
                0,
                'male',
                0,
                5000
            ],
            // 1 hour trip, without parking but with 30 minutes of bonus
            [
                date_create(),
                date_create()->add(new \DateInterval('PT1H')),
                0,
                'male',
                30,
                840
            ],
            // two hours trip by a female in the night
            [
                date_create('00:00:00'),
                date_create('02:00:00'),
                0,
                'female',
                0,
                1200
            ],
            // two hours trip by a male in the night
            [
                date_create('00:00:00'),
                date_create('02:00:00'),
                0,
                'male',
                0,
                2400
            ],
            // 23 hours by a male between two days
            [
                date_create('2015-08-11 05:36:09'),
                date_create('2015-08-12 05:31:44'),
                58988,
                'male',
                0,
                5000
            ]
        ];
    }

    /**
     * @dataProvider computeCostProvider
     *
     * @param DateTime $tripBeginning
     * @param DateTime $tripEnd
     * @param int $tripParkSeconds
     * @param string $customerGender could be 'male' or 'female'
     * @param int $customerBonus
     * @param int cost in eurocents
     */
    public function testComputeCost(
        \DateTime $tripBeginning,
        \DateTime $tripEnd,
        $tripParkSeconds,
        $customerGender,
        $customerBonus,
        $cost
    ) {
        $computedCost = $this->tripCostComputerService->computeCost(
            $tripBeginning,
            $tripEnd,
            $tripParkSeconds,
            $customerGender,
            $customerBonus
        );

        $this->assertEquals($cost, $computedCost);
    }
}

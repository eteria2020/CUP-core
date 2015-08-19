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
        $this->httpClient = \Mockery::mock('Zend\Http\Client');
        $this->url = \Mockery::mock('Zend\View\Helper\Url');
        $this->cartasiContractsService = \Mockery::mock('Cartasi\Service\CartasiContractsService');
        $this->transactionsRepository = \Mockery::mock('Cartasi\Entity\Repository\TransactionsRepository');
        $this->emailService = \Mockery::mock('SharengoCore\Service\EmailService');
        $this->cartasiCustomerPaymentsService = \Mockery::mock('Cartasi\Service\CartasiCustomerPayments');

        $this->tripCostService = new TripCostService(
            $this->faresService,
            $this->tripFaresService,
            $this->entityManager,
            /*$this->httpClient,
            $this->url,
            $this->cartasiContractsService,
            $this->transactionsRepository,
            [
                'uri' => 'my.uri'
            ],*/
            $this->emailService,
            $this->cartasiCustomerPaymentsService
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

    public function testUnpayableConsequences()
    {
        $consequencesMethod = new \ReflectionMethod('SharengoCore\Service\TripCostService', 'unpayableConsequences');
        $consequencesMethod->setAccessible(true);

        $customer = \Mockery::mock('SharengoCore\Entity\Customers');
        $customer->shouldReceive('disable');

        $tripPayment = \Mockery::mock('SharengoCore\Entity\TripPayments');
        $tripPayment->shouldReceive('setWrongPayment');

        $this->entityManager->shouldReceive('persist')->with($customer);
        $this->entityManager->shouldReceive('persist')->with($tripPayment);
        $this->entityManager->shouldReceive('flush');

        $customer->shouldReceive('getName');
        $customer->shouldReceive('getSurname');

        $consequencesMethod->invoke($this->tripCostService, $customer, $tripPayment);
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use Cartasi\Entity\Transactions;

use Zend\Http\Response;

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

        $this->tripCostService = new TripCostService(
            $this->faresService,
            $this->tripFaresService,
            $this->entityManager,
            $this->httpClient,
            $this->url,
            $this->cartasiContractsService,
            $this->transactionsRepository,
            [
                'uri' => 'my.uri'
            ],
            $this->emailService
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

    public function sendPaymentProvider()
    {
        return [
            [123, 200, false, 'KO', null],
            [123, 404, false, 'KO', null],
            [123, 200, true, 'OK', new Transactions()],
            [null, 200, false, 'KO', null]
        ];
    }

    /**
     * @dataProvider sendPaymentProvider
     *
     * @param int
     * @param int http response status code
     * @param boolean
     * @param string 'OK' or 'KO'
     * @param null|Transactions
     */
    public function testSendPaymentRequest(
        $contractNumber,
        $statusCode,
        $completedCorrectly,
        $outcome,
        $transaction
    ) {
        $sendRequestMethod = new \ReflectionMethod('SharengoCore\Service\TripCostService', 'sendPaymentRequest');
        $sendRequestMethod->setAccessible(true);

        $customer = \Mockery::mock('SharengoCore\Entity\Customers');

        $this->cartasiContractsService->shouldReceive('getCartasiContractNumber')
            ->with($customer)->andReturn($contractNumber);

        $url = 'url';
        $this->url->shouldReceive('__invoke')->andReturn($url);

        $avoidCartasiProperty = new \ReflectionProperty('SharengoCore\Service\TripCostService', 'avoidCartasi');
        $avoidCartasiProperty->setAccessible(true);
        $avoidCartasiProperty->setValue($this->tripCostService, false);

        $response = new Response();
        $response->setStatusCode($statusCode);
        $body = json_encode([
            'outcome' => $outcome,
            'codTrans' => 123
        ]);
        $response->setContent($body);

        $this->httpClient->shouldReceive('send')->andReturn($response);

        $this->transactionsRepository->shouldReceive('findOneById')->andReturn($transaction);

        $ret = new \StdClass;
        $ret->completedCorrectly = $completedCorrectly;
        $ret->outcome = $outcome;
        $ret->transaction = $transaction;

        $this->assertEquals(
            $ret,
            $sendRequestMethod->invoke($this->tripCostService, $customer, 1234)
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

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Fares;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripPaymentTries;
use Cartasi\Entity\Transactions;
use Cartasi\Entity\CartasiResponse;

use Doctrine\DBAL\Connection;

class PaymentsServiceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cartasiCustomerPayments = \Mockery::mock('Cartasi\Service\CartasiCustomerPaymentsInterface');
        $this->cartasiContractsService = \Mockery::mock('Cartasi\Service\CartasiContractsService');
        $this->entityManager = \Mockery::mock('Doctrine\ORM\EntityManager');
        $this->emailService = \Mockery::mock('SharengoCore\Service\EmailService');
        $this->eventManager = \Mockery::mock('Zend\EventManager\EventManager');
        $this->tripPaymentTriesService = \Mockery::mock('SharengoCore\Service\TripPaymentTriesService');
        $this->customerDeactivationService = \Mockery::mock('SharengoCore\Service\CustomerDeactivationService');

        $this->paymentsService = new PaymentsService(
            $this->cartasiCustomerPayments,
            $this->cartasiContractsService,
            $this->entityManager,
            $this->emailService,
            $this->eventManager,
            $this->tripPaymentTriesService,
            '',
            $this->customerDeactivationService
        );
    }

    public function testUnpayableConsequences()
    {
        $consequencesMethod = new \ReflectionMethod('SharengoCore\Service\PaymentsService', 'unpayableConsequences');
        $consequencesMethod->setAccessible(true);

        $customer = \Mockery::mock('SharengoCore\Entity\Customers');
        $customer->shouldReceive('setPaymentAble')->with(false);

        $tripPayment = \Mockery::mock('SharengoCore\Entity\TripPayments');
        $tripPayment->shouldReceive('setWrongPayment');

        $tripPaymentTry = \Mockery::mock('SharengoCore\Entity\tripPaymentTries');

        $this->customerDeactivationService->shouldReceive('deactivateForTripPaymentTry')->with(
            $customer,
            $tripPaymentTry
        );

        $this->entityManager->shouldReceive('persist')->with($customer);
        $this->entityManager->shouldReceive('persist')->with($tripPayment);
        $this->entityManager->shouldReceive('flush');

        $this->eventManager->shouldReceive('trigger')->with(
            'wrongTripPayment',
            $this->paymentsService,
            [
                'customer' => $customer,
                'tripPayment' => $tripPayment
            ]
        );

        $customer->shouldReceive('getName');
        $customer->shouldReceive('getSurname');

        $consequencesMethod->invoke($this->paymentsService, $customer, $tripPayment, $tripPaymentTry, true);
    }

    public function testTryPaymentNoContractPaymentAble()
    {
        $customer = new Customers();
        $customer->setPaymentAble(true);
        $trip = new Trips();
        $trip->setCustomer($customer);
        $fare = new Fares(0, 0, '{}');
        $tripPayment = new TripPayments($trip, $fare, 10, 0, 0, 1);

        $this->cartasiContractsService->shouldReceive('hasCartasiContract')->andReturn(false);
        $this->eventManager->shouldReceive('trigger')->with(
            'notifyCustomerPay',
            $this->paymentsService,
            [
                'customer' => $customer,
                'tripPayment' => $tripPayment
            ]
        );
        $this->entityManager->shouldReceive('persist')->with($customer);
        $this->entityManager->shouldReceive('flush');

        $this->paymentsService->tryPayment($tripPayment);
    }

    public function testTryPaymentNoContractNotPaymentAble()
    {
        $customer = new Customers();
        $customer->setPaymentAble(false);
        $trip = new Trips();
        $trip->setCustomer($customer);
        $fare = new Fares(0, 0, '{}');
        $tripPayment = new TripPayments($trip, $fare, 10, 0, 0, 1);

        $this->cartasiContractsService->shouldReceive('hasCartasiContract')->andReturn(false);
        $this->entityManager->shouldReceive('persist')->with($customer);
        $this->entityManager->shouldReceive('flush');

        $this->paymentsService->tryPayment($tripPayment);
    }

    public function testTryPaymentWithContract()
    {
        $customer = new Customers();
        $trip = new Trips();
        $trip->setCustomer($customer);
        $fare = new Fares(0, 0, '{}');
        $tripPayment = new TripPayments($trip, $fare, 10, 0, 0, 1);

        $this->cartasiContractsService->shouldReceive('hasCartasiContract')->andReturn(true);
        $transaction = new Transactions();
        $response = new CartasiResponse(true, 'OK', $transaction);
        $this->cartasiCustomerPayments->shouldReceive('sendPaymentRequest')->andReturn($response);
        $this->entityManager->shouldReceive('beginTransaction');
        $tripPaymentTry = new TripPaymentTries(
            $tripPayment,
            $response->getOutcome(),
            $response->getTransaction()
        );
        $this->tripPaymentTriesService->shouldReceive('generateTripPaymentTry')->andReturn($tripPaymentTry);
        $this->entityManager->shouldReceive('persist')->with($tripPayment);
        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('persist')->with($tripPaymentTry);
        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('commit');

        $this->paymentsService->tryPayment($tripPayment);
    }
}

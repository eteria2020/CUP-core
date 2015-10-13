<?php

namespace SharengoCore\Service;

class PaymentsServiceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cartasiCustomerPayments = \Mockery::mock('Cartasi\Service\CartasiCustomerPayments');
        $this->cartasiContractsService = \Mockery::mock('Cartasi\Service\CartasiContractsService');
        $this->entityManager = \Mockery::mock('Doctrine\ORM\EntityManager');
        $this->emailService = \Mockery::mock('SharengoCore\Service\EmailService');
        $this->eventManager = \Mockery::mock('Zend\EventManager\EventManager');
        $this->tripPaymentTriesService = \Mockery::mock('SharengoCore\Service\TripPaymentTriesService');

        $this->paymentsService = new PaymentsService(
            $this->cartasiCustomerPayments,
            $this->cartasiContractsService,
            $this->entityManager,
            $this->emailService,
            $this->eventManager,
            $this->tripPaymentTriesService,
            ''
        );
    }

    public function testUnpayableConsequences()
    {
        $consequencesMethod = new \ReflectionMethod('SharengoCore\Service\PaymentsService', 'unpayableConsequences');
        $consequencesMethod->setAccessible(true);

        $customer = \Mockery::mock('SharengoCore\Entity\Customers');
        $customer->shouldReceive('disable');
        $customer->shouldReceive('setPaymentAble')->with(false);

        $tripPayment = \Mockery::mock('SharengoCore\Entity\TripPayments');
        $tripPayment->shouldReceive('setWrongPayment');

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

        $consequencesMethod->invoke($this->paymentsService, $customer, $tripPayment, true);
    }
}

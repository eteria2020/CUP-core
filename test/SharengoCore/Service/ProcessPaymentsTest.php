<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Fares;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Listener\PaymentEmailListener;
use SharengoCore\Listener\NotifyCustomerPayListener;

use Mockery as M;
use Zend\EventManager\EventManager;

class ProcessPaymentsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->paymentsService = M::mock('SharengoCore\Service\PaymentsService');

        $this->processPaymentsService = new ProcessPaymentsService(
            new EventManager('ProcessPaymentsService'),
            new BlackHoleLogger(),
            new PaymentEmailListener('', ''),
            new NotifyCustomerPayListener('', ''),
            $this->paymentsService
        );
    }

    public function testProcessPaymentsOk()
    {
        $trip = new Trips();
        $fare = new Fares(30, 10, '');

        $tripPayments = [
            new TripPayments($trip, $fare, 10, 20, 5, 100),
            new TripPayments($trip, $fare, 20, 30, 12, 230)
        ];

        $this->paymentsService->shouldReceive('tryPayment')->twice();

        $this->processPaymentsService->processPayments($tripPayments);
    }

    public function testProcessPaymentsfail()
    {
        $trip = new Trips();
        $fare = new Fares(30, 10, '');

        $tripPayments = [
            new TripPayments($trip, $fare, 10, 20, 5, 100),
            new TripPayments($trip, $fare, 20, 30, 12, 230)
        ];

        $this->paymentsService->shouldReceive('tryPayment')->once()->andThrow(
            'Cartasi\Exception\WrongPaymentException'
        );

        $this->processPaymentsService->processPayments($tripPayments);
    }
}

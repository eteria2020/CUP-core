<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\ExtraPayment;
use SharengoCore\Entity\Invoices;

class ExtraPaymentsServiceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->entityManager = \Mockery::mock('Doctrine\ORM\EntityManager');
        $this->invoicesService = \Mockery::mock('SharengoCore\Service\InvoicesService');

        $this->extraPayments = new ExtraPaymentsService(
            $this->entityManager,
            $this->invoicesService
        );
    }

    public function testGenerateInvoice()
    {
        $customer = new Customers();
        $fleet = new Fleet(42, 'Gigi', 0, 0, 15, true);
        $amount = 42;
        $paymentType = 'extra';
        $reason = 'interstellar voyages are not allowed';

        $extraPayment = new ExtraPayment($customer, $fleet, $amount, $paymentType, $reason);

        $this->entityManager->shouldReceive('beginTransaction');

        $invoice = Invoices::createInvoiceForExtraOrPenalty($customer, $fleet, 1, $reason, $amount);

        $this->invoicesService->shouldReceive('prepareInvoiceForExtraOrPenalty')
            ->with($customer, $fleet, $reason, $amount)
            ->andReturn($invoice);

        $this->entityManager->shouldReceive('persist')->with($invoice);
        $this->entityManager->shouldReceive('persist')->with($extraPayment);

        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('commit');

        $this->extraPayments->generateInvoice($extraPayment);

        $this->assertEquals($invoice, $extraPayment->getInvoice());
    }
}

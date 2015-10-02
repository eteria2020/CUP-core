<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ExtraPayment;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\InvoicesService;
use SharengoCore\Entity\Fleet;

use Doctrine\ORM\EntityManager;

class ExtraPaymentsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var InvoicesService
     */
    private $invoicesService;

    public function __construct(
        EntityManager $entityManager,
        InvoicesService $invoicesService
    ) {
        $this->entityManager = $entityManager;
        $this->invoicesService = $invoicesService;
    }

    /**
     * @param Customers $customer
     * @param int $amount
     * @param string $paymentType
     * @param string $reason
     * @return ExtraPayment
     */
    public function registerExtraPayment(
        Customers $customer,
        Fleet $fleet,
        $amount,
        $paymentType,
        $reason
    ) {
        $extraPayment = new ExtraPayment(
            $customer,
            $fleet,
            $amount,
            $paymentType,
            $reason
        );

        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }

    /**
     * @param Customers $customer
     * @param string $reason
     * @param int $amount in eurocents
     */
    public function generateInvoice(Customers $customer, $reason, $amount)
    {
        $invoice = $this->invoicesService->prepareInvoiceForExtraOrPenalty($customer, $reason, $amount);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }
}

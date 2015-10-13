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
     * @param ExtraPayment $extraPayment
     * @param bool $doFlush
     */
    public function generateInvoice(
        ExtraPayment $extraPayment,
        $doCommit = true
    ) {
        $this->entityManager->beginTransaction();

        try {
            // create the invoice
            $invoice = $this->invoicesService->prepareInvoiceForExtraOrPenalty(
                $extraPayment->getCustomer(),
                $extraPayment->getFleet(),
                $extraPayment->getReason(),
                $extraPayment->GetAmount()
            );

            $this->entityManager->persist($invoice);

            // associate the invoice with the extra payment
            $extraPayment->associateInvoice($invoice);

            $this->entityManager->persist($extraPayment);

            if ($doCommit) {
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
        }
    }
}

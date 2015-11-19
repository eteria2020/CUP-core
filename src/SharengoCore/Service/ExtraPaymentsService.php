<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ExtraPayment;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\InvoicesService;
use SharengoCore\Entity\Fleet;
use Cartasi\Entity\Transactions;

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

    /**
     * @param EntityManager $entityManager
     * @param InvoicesService $invoicesService
     */
    public function __construct(
        EntityManager $entityManager,
        InvoicesService $invoicesService
    ) {
        $this->entityManager = $entityManager;
        $this->invoicesService = $invoicesService;
    }

    /**
     * @param Customers $customer
     * @param Fleet $fleet
     * @param Transactions $transaction
     * @param int $amount
     * @param string $type
     * @param string[] $reasons
     * @param integer[] $amounts
     * @return ExtraPayment
     */
    public function registerExtraPayment(
        Customers $customer,
        Fleet $fleet,
        Transactions $transaction,
        $amount,
        $type,
        $reasons,
        $amounts
    ) {
        $reasonsAmounts = [];
        for ($i = 0; $i < $reasons->count(); $i++) {
            if (!array_key_exists($reasons[$i], $reasonsAmounts)) {
                $reasonsAmounts[$reasons[$i]] = $amounts[$i];
            } else {
                $timesInReasons = array_count_values($reasons)[$reasons[$i]];
                for ($j = 2; $j < $timesInReasons + 1; $j++) {
                    if (!array_key_exists($reasons[$i] . $j, $reasonsAmounts)) {
                        $reasonsAmounts[$reasons[$i] . $j] = $amounts[$i];
                        break;
                    }
                }
            }
        }

        $extraPayment = new ExtraPayment(
            $customer,
            $fleet,
            $transaction,
            $amount,
            $paymentType,
            $reasonsAmounts
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

    /**
     * Returns an array containing all types of ExtraPayments
     *
     * @return string[]
     */
    public function getAllTypes()
    {
        return [
            "extra",
            "penalty"
        ];
    }
}

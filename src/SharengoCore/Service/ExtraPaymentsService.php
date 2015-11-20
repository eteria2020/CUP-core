<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ExtraPayment;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\InvoicesService;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Queries\AllExtraPaymentTypes;
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
        // Fill reasonsAmounts with key value pairs from reasons and amounts
        // using reasons as key and amount as value
        for ($i = 0; $i < count($reasons); $i++) {
            // Check if reason has not already been used as key
            if (!array_key_exists($reasons[$i], $reasonsAmounts)) {
                $reasonsAmounts[$reasons[$i]] = $this->formatAmount($amounts[$i]);
            } else {
                $j = 2;
                while (array_key_exists($reasons[$i] . ' - ' . $j, $reasonsAmounts)) {
                    $j++;
                }
                $reasonsAmounts[$reasons[$i] . ' - ' . $j] = $this->formatAmount($amounts[$i]);
            }
        }

        $extraPayment = new ExtraPayment(
            $customer,
            $fleet,
            $transaction,
            $amount,
            $type,
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
                $extraPayment->getReasons(),
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
        $query = new AllExtraPaymentTypes($this->entityManager);
        $result = [];
        foreach ($query() as $value) {
            array_push($result, $value['type']);
        }
        return $result;
    }

    /**
     * @param string $amount
     * @return string
     */
    private function formatAmount($amount)
    {
        return sprintf('%.2f', intval($amount) / 100);
    }
}

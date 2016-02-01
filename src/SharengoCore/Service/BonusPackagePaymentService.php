<?php

namespace SharengoCore\Service;

use Cartasi\Entity\Transactions;
use SharengoCore\Entity\Queries\BonusPackagePaymentByTransaction;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\Queries\BonusPackagePaymentToInvoice;
use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Entity\BonusPackagePayment;
use SharengoCore\Service\InvoicesService;

use Doctrine\ORM\EntityManagerInterface;

class BonusPackagePaymentService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var InvoicesService
     */
    private $invoiceService;

    public function __construct(
        EntityManagerInterface $entityManager,
        InvoicesService $invoiceService
    ) {
        $this->entityManager = $entityManager;
        $this->invoiceService = $invoiceService;
    }

    /**
     * @param Transactions $transaction
     * @return bool
     */
    public function transactionAlreadyUsed(Transactions $transaction)
    {
        $query = new BonusPackagePaymentByTransaction($transaction, $this->entityManager);

        return $query() instanceof BonusPackagePayment;
    }

    /**
     * @return BonusPackagePayment[]
     */
    public function getBonusPackagePaymentsToInvoice()
    {
        $query = new BonusPackagePaymentToInvoice($this->entityManager);

        return $query();
    }

    /**
     * @param BonusPackagePayment $bonusPayment
     * @param bool $doCommit
     */
    public function generateInvoice(
        BonusPackagePayment $bonusPayment,
        $doCommit = true
    ) {
        $this->entityManager->beginTransaction();

        try {
            $invoice = $this->invoiceService->prepareInvoiceForBonusPackagePayment(
                $bonusPayment
            );

            $this->entityManager->persist($invoice);

            $bonusPayment->associateInvoice($invoice);

            $this->entityManager->persist($bonusPayment);

            if ($doCommit) {
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
        }
    }
}

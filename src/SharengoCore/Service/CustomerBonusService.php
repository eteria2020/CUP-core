<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\CustomerBonusByTransaction;
use Cartasi\Entity\Transactions;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\Queries\CustomerBonusForInvoice;
use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Service\InvoicesService;

use Doctrine\ORM\EntityManagerInterface;

class CustomerBonusService
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
        $query = new CustomerBonusByTransaction($transaction, $this->entityManager);

        return $query() instanceof CustomersBonus;
    }

    /**
     * @return CustomersBonus[]
     */
    public function getBonusPaymentsForInvoice()
    {
        $query = new CustomerBonusForInvoice($this->entityManager);

        return $query();
    }

    /**
     * @param CustomersBonus $bonus
     * @param bool $doCommit
     */
    public function generateInvoice(
        CustomersBonus $bonus,
        $doCommit = true
    ) {
        $this->entityManager->beginTransaction();

        try {
            // check if bonus comes from a package
            if (!$bonus->getPackage() instanceof CustomersBonusPackages) {
                throw new \Exception('bonus not invoiceable');
            }

            $invoice = $this->invoiceService->prepareInvoiceForBonusPackage(
                $bonus->getCustomer(),
                $bonus->getPackage()
            );

            $this->entityManager->persist($invoice);

            $bonus->associateInvoice($invoice);

            $this->entityManager->persist($bonus);

            if ($doCommit) {
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
        }
    }
}

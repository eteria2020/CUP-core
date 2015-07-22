<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\InvoicesRepository;
use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Invoices;

class InvoicesService
{
    /**
     * @var InvoicesRepository
     */
    private $invoicesRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var mixed
     */
    private $invoiceConfig;

    /**
     * @param EntityRepository $invoicesRepository
     * @param EntityManager $entityManager
     * @param mixed $invoiceConfig
     */
    public function __construct(
        InvoicesRepository $invoicesRepository,
        EntityManager $entityManager,
        $invoiceConfig
    ) {
        $this->invoicesRepository = $invoicesRepository;
        $this->entityManager = $entityManager;
        $this->invoiceConfig = $invoiceConfig;
    }

    /**
     * @return mixed
     */
    public function getListInvoices()
    {
        return $this->invoicesRepository->findAll();
    }

    /**
     * @param \SharengoCore\Entity\Customers $customer
     * @return mixed
     */
    public function getCustomersInvoicesFirstPayment($customer)
    {
        return $this->invoicesRepository->findByCustomerFirstPayment($customer);
    }

    /**
     * @var \SharengoCore\Entity\Customers
     */
    public function createInvoiceForFirstPayment($customer)
    {
        $invoice = Invoices::createInvoiceForFirstPayment($customer, $invoiceConfig['template_version']);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }
}

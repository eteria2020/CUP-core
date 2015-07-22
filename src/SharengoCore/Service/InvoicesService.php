<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\InvoicesRepository;
use SharengoCore\Entity\Invoices;

class InvoicesService
{
    /**
     * @var InvoicesRepository
     */
    private $invoicesRepository;

    /**
     * @var string
     */
    private $templateVarsion;

    /**
     * @param EntityRepository $invoicesRepository
     * @param mixed $invoiceConfig
     */
    public function __construct(
        InvoicesRepository $invoicesRepository,
        $invoiceConfig
    ) {
        $this->invoicesRepository = $invoicesRepository;
        $this->templateVarsion = $invoiceConfig['template_version'];
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
        return Invoices::createInvoiceForFirstPayment($customer, $this->templateVarsion);
    }
}

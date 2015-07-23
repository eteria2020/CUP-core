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
     * @param integer $id
     * @return Invoices
     */
    public function getInvoiceById($id)
    {
        return $this->invoicesRepository->findOneById($id);
    }

    /**
     * @var \SharengoCore\Entity\Customers
     */
    public function createInvoiceForFirstPayment($customer)
    {
        return Invoices::createInvoiceForFirstPayment($customer, $this->templateVarsion);
    }

    /**
     * @param  Invoices $invoice
     */
    public function generateHtmlFromInvoice($invoice)
    {
        // generate html from invoice

        // return html
    }
}

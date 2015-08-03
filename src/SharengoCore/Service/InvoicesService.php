<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\InvoicesRepository;
use SharengoCore\Entity\Invoices;
use SharengoCore\Service\DatatableService;

class InvoicesService
{
    /**
     * @var InvoicesRepository
     */
    private $invoicesRepository;

    /**
     * @var string
     */
    private $templateVersion;

    /**
     * @var integer
     */
    private $subscriptionAmount;

    /**
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @param EntityRepository $invoicesRepository
     * @param mixed $invoiceConfig
     */
    public function __construct(
        InvoicesRepository $invoicesRepository,
        $invoiceConfig,
        DatatableService $datatableService
    ) {
        $this->invoicesRepository = $invoicesRepository;
        $this->templateVersion = $invoiceConfig['template_version'];
        $this->subscriptionAmount = $invoiceConfig['subscription_amount'];
        $this->datatableService = $datatableService;
    }

    public function getTotalInvoices()
    {
        return $this->invoicesRepository->getTotalInvoices();
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
     * @param \SharengoCore\Entity\Customers
     * @return Invoices
     */
    public function prepareInvoiceForFirstPayment($customer)
    {
        return Invoices::createInvoiceForFirstPayment(
            $customer,
            $this->templateVersion,
            $this->calculateAmountsWithTaxesFromTotal($this->subscriptionAmount)
        );
    }

    /**
     * @param Customers $customer
     * @param integer $date
     * @return mixed
     */
    public function getInvoicesByCustomerWithDate($customer, $date = null)
    {
        // if no date param is given
        if ($date == null) {
            return $this->invoicesRepository->findByCustomer($customer);
        // if date param has no day (YYYYMM)
        } elseif ($date < 10000000) {
            return $this->invoicesRepository->findInvoicesByCustomerWithDateNoDay($customer, $date);
        // if date param is complete with day (YYYYMMDD)
        } else {
            return $this->invoicesRepository->findInvoicesByCustomerWithDate($customer, $date);
        }
    }

    /**
     * @param Customers $customer
     * @return mixed
     */
    public function getDistinctDatesForCustomerByMonth($customer)
    {
        $dates = $this->invoicesRepository->findDistinctDatesForCustomerByMonth($customer);
        $returnDates = [];
        foreach ($dates as $date) {
            array_push($returnDates, $date[1]);
        }
        return $returnDates;
    }

    /**
     * @param mixed $filters
     * @return mixed
     */
    public function getDataDataTable($filters)
    {
        $invoices = $this->datatableService->getData('Invoices', $filters);

        return array_map(function (Invoices $invoice) {
            return [
                'e' => [
                    'invoiceNumber' => $invoice->getInvoiceNumber(),
                    'invoiceDate' => $invoice->getInvoiceDate(),
                    'type' => $invoice->getType(),
                    'amount' => $invoice->getAmount()
                ],
                'link' => $this->generateLink($invoice->getId())
            ];
        }, $invoices);
    }

    /**
     * @param integer $amount
     * @return mixed
     */
    private function calculateAmountsWithTaxesFromTotal($amount)
    {
        $amounts = [];

        // calculate amounts
        $iva = (integer) ($amount / 122 * 22);
        $total = $amount - $iva;

        // format amounts
        $amounts['iva'] = $this->parseDecimal($iva);
        $amounts['total'] = $this->parseDecimal($total);
        $amounts['grand_total'] = $this->parseDecimal($amount);

        $amounts['grand_total_cents'] = $amount;

        return $amounts;
    }

    /**
     * @param integer
     * @return string
     */
    private function parseDecimal($decimal)
    {
        return number_format((float) $decimal / 100, 2, ',', '');
    }

    private function generateLink($id)
    {
        // TODO get correct path (needs route in Core)
        return 'path/' . $id;
    }
}

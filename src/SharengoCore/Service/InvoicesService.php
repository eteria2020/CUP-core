<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\InvoicesRepository;
use SharengoCore\Entity\Invoices;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\SimpleLoggerService as Logger;

use Doctrine\ORM\EntityManager;

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
     * @var integer
     */
    private $ivaPercentage;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityRepository $invoicesRepository
     * @param mixed $invoiceConfig
     */
    public function __construct(
        InvoicesRepository $invoicesRepository,
        Logger $logger,
        EntityManager $entityManager,
        $invoiceConfig
    ) {
        $this->invoicesRepository = $invoicesRepository;
        $this->templateVersion = $invoiceConfig['template_version'];
        $this->subscriptionAmount = $invoiceConfig['subscription_amount'];
        $this->ivaPercentage = $invoiceConfig['iva_percentage'];
        $this->logger = $logger;
        $this->entityManager = $entityManager;
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
     * @param Customers
     * @return Invoices
     */
    public function prepareInvoiceForFirstPayment(Customers $customer)
    {
        return Invoices::createInvoiceForFirstPayment(
            $customer,
            $this->templateVersion,
            $this->calculateAmountsWithTaxesFromTotal($this->subscriptionAmount)
        );
    }

    public function createInvoicesForTrips($tripPayments, $writeToDb = true)
    {
        $invoices = [];

        // loop through each day
        foreach ($tripPayments as $dateKey => $tripPaymentsForDate) {
            // generate date for invoices
            $date = date_create_from_format('Y-m-d', $dateKey);
            $this->logger->log("Generating invoices for date: " . $dateKey . "\n\n");
            // loop through each customer in day
            foreach ($tripPaymentsForDate as $customerId => $tripPaymentsForCustomer) {
                $this->logger->log("Generating invoice for customer: " . $customerId . "\n");
                // get customer for group of tripPayments
                $customer = $tripPaymentsForCustomer[0]->getTrip()->getCustomer();
                // generate invoice from group of tripPayments
                $invoice = $this->prepareInvoiceForTrips($customer, $tripPaymentsForCustomer);
                $this->logger->log("Invoice created: " . $invoice->getId() . "\n");
                $this->entityManager->persist($invoice);
                $this->logger->log("EntityManager: invoice persisted\n");
                array_push($invoices, $invoice);
                $this->logger->log("Updating tripPayments with invoice...\n\n");
                foreach ($tripPaymentsForCustomer as $tripPayment) {
                    $tripPayment->setInvoice($invoice)
                        ->setInvoicedAt(date_create());
                    $this->entityManager->persist($tripPayment);
                }
            }
        }

        // save invoices to db
        if ($writeToDb) {
            $this->logger->log("EntityManager: about to flush\n");
            $this->entityManager->flush();
            $this->logger->log("EntityManager: flushed\n");
        }

        return $invoices;
    }

    /**
     * @param Customers
     * @param TripPayments[] $tripPayments
     * @return Invoices
     */
    public function prepareInvoiceForTrips(Customers $customer, $tripPayments)
    {
        $total = 0;
        foreach ($tripPayments as $tripPayment) {
            $total += $tripPayment->getTotalCost();
        }

        return Invoices::createInvoiceForTrips(
            $customer,
            $tripPayments,
            $this->templateVersion,
            $this->calculateAmountsWithTaxesFromTotal($total)
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
     * @param integer $amount
     * @return mixed
     */
    private function calculateAmountsWithTaxesFromTotal($amount)
    {
        $amounts = [];

        // calculate amounts
        $iva = (integer) ($amount / 122 * $this->ivaPercentage);
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
}

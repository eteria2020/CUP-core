<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\InvoicesRepository;
use SharengoCore\Entity\Invoices;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Cards;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\BonusPackagePayment;
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
     * @var DatatableServiceInterface
     */
    private $datatableService;

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
        DatatableServiceInterface $datatableService,
        EntityManager $entityManager,
        Logger $logger,
        $invoiceConfig
    ) {
        $this->invoicesRepository = $invoicesRepository;
        $this->datatableService = $datatableService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->templateVersion = $invoiceConfig['template_version'];
        $this->subscriptionAmount = $invoiceConfig['subscription_amount'];
        $this->ivaPercentage = $invoiceConfig['iva_percentage'];
    }

    /**
     * @return Invoices[]
     */
    public function getAllInvoices()
    {
        return $this->invoicesRepository->findAll();
    }

    /**
     * @return integer
     */
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
     * @param Fleet | null $fleet
     * @return Invoices[]
     */
    public function getInvoicesByFleetJoinCustomers($fleet = null)
    {
        return $this->invoicesRepository->findInvoicesByFleetJoinCustomers($fleet);
    }

    /**
     * @param \DateTime $date
     * @param Fleet | null $fleet
     * @return Invoices[]
     */
    public function getInvoicesByDateAndFleetJoinCustomers(\DateTime $date, $fleet = null)
    {
        return $this->invoicesRepository->findInvoicesByDateAndFleetJoinCustomers($date, $fleet);
    }

    /**
     * @param Invoices[] $invoices
     * @return array[Invoices[]]
     */
    public function groupByInvoiceDate($invoices)
    {
        $groupedInvoices = [];
        foreach ($invoices as $invoice) {
            if (array_key_exists($invoice->getInvoiceDate(), $groupedInvoices)) {
                array_push($groupedInvoices[$invoice->getInvoiceDate()], $invoice);
            } else {
                $groupedInvoices[$invoice->getInvoiceDate()] = [$invoice];
            }
        }
        return $groupedInvoices;
    }

    /**
     * @param Customers
     * @return Invoices
     */
    public function prepareInvoiceForFirstPayment(Customers $customer)
    {
        $amounts = [
            "sum" => $this->calculateAmountsWithTaxesFromTotal(
                $customer->payedSubscriptionAmount($this->subscriptionAmount)
            ),
            "iva" => $this->ivaPercentage
        ];
        return Invoices::createInvoiceForFirstPayment(
            $customer,
            $this->templateVersion,
            $amounts
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

                // loop through each fleet for customer
                foreach ($tripPaymentsForCustomer as $fleetId => $tripPaymentsForFleet) {
                    $this->logger->log("Generating invoice for fleet: " . $fleetId . "\n");
                    // get customer for group of tripPayments
                    $customer = $tripPaymentsForFleet[0]->getTrip()->getCustomer();
                    // generate invoice from group of tripPayments
                    $invoice = $this->prepareInvoiceForTrips($customer, $tripPaymentsForFleet);
                    $this->logger->log("Invoice created: " . $invoice->getId() . "\n");
                    $this->entityManager->persist($invoice);
                    $this->logger->log("EntityManager: invoice persisted\n");
                    array_push($invoices, $invoice);
                    $this->logger->log("Updating tripPayments with invoice...\n\n");
                    foreach ($tripPaymentsForFleet as $tripPayment) {
                        $tripPayment->setInvoice($invoice);
                        $this->entityManager->persist($tripPayment);
                    }
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
        $rowAmounts = [];

        $total = 0;
        // calculate amounts for single rows and add them to total
        foreach ($tripPayments as $tripPayment) {
            array_push($rowAmounts, $this->parseDecimal($tripPayment->getTotalCost()));
            $total += $tripPayment->getTotalCost();
        }

        // create invoice
        return Invoices::createInvoiceForTrips(
            $customer,
            $tripPayments,
            $this->templateVersion,
            [
                'sum' => $this->calculateAmountsWithTaxesFromTotal($total),
                'rows' => $rowAmounts,
                'iva' => $this->ivaPercentage
            ]
        );
    }

    /**
     * @param Customers $customer
     * @param BonusPackages $bonusPackage
     * @param Fleet $fleet
     * @return Invoices
     */
    public function prepareInvoiceForBonusPackagePayment(BonusPackagePayment $bonusPayment)
    {
        $amounts = [
            'sum' => $this->calculateAmountsWithTaxesFromTotal($bonusPayment->getAmount()),
            'iva' => $this->ivaPercentage
        ];

        return Invoices::createInvoiceForBonusPackage(
            $bonusPayment->getCustomer(),
            $bonusPayment->getPackage(),
            $bonusPayment->getFleet(),
            $this->templateVersion,
            $amounts
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
     * This method return an array containing the DataTable filters,
     * from a Session Container defined in the SessionDatatableSerivce.
     *
     * @return array
     */
    public function getDataTableSessionFilters()
    {
        return $this->datatableService->getSessionFilter('Invoices');
    }

    /**
     * @param mixed $filters
     * @return mixed
     */
    public function getDataDataTable(array $filters = [], $count = false)
    {
        $invoices = $this->datatableService->getData('Invoices', $filters, $count);

        if ($count) {
            return $invoices;
        }

        return array_map(function (Invoices $invoice) {
            return [
                'e' => [
                    'invoiceNumber' => $invoice->getInvoiceNumber(),
                    'invoiceDate' => $invoice->getInvoiceDate(),
                    'type' => $invoice->getType(),
                    'amount' => $invoice->getAmount(),
                ],
                'cu' => [
                    'id' => $invoice->getCustomer()->getId(),
                    'name' => $invoice->getCustomer()->getName(),
                    'surname' => $invoice->getCustomer()->getSurname()
                ],
                'link' => $invoice->getId()
            ];
        }, $invoices);
    }

    /**
     * @param mixed $filters
     * @return integer
     */
    public function getTotalDatatableInvoices($filters)
    {
        if (!empty($filters['fixedColumn']) &&
            !empty($filters['fixedValue']) &&
            !empty($filters['fixedLike'])
        ) {
            return $this->invoicesRepository->findTotalDatatableInvoices(
                $filters['fixedColumn'],
                $filters['fixedValue'],
                $filters['fixedLike']
            );
        } else {
            return $this->getTotalInvoices();
        }
    }

    /**
     * @param integer $amount
     * @return mixed
     */
    public function calculateAmountsWithTaxesFromTotal($amount)
    {
        $amounts = [];

        // calculate amounts
        $iva = (integer) ($amount / (100 + $this->ivaPercentage) * $this->ivaPercentage);
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

    /**
     * @param Customers $customer
     * @param Fleet $fleet
     * @param array[] $reasons
     * @param integer amount in eurocents
     * @return Invoices
     */
    public function prepareInvoiceForExtraOrPenalty(
        Customers $customer,
        Fleet $fleet,
        $reasons,
        $amount
    ) {
        $amounts = [
            'sum' => [
                'iva' => $this->parseDecimal(0),
                'total' => $this->parseDecimal($amount),
                'grand_total' => $this->parseDecimal($amount),
                'grand_total_cents' => $amount
                ],
            'iva' => 0
        ];

        return Invoices::createInvoiceForExtraOrPenalty(
            $customer,
            $fleet,
            $this->templateVersion,
            $reasons,
            $amounts
        );
    }

    /**
     * @param Invoices $invoice
     * @return string
     */
    public function getExportDataForInvoice($invoice)
    {
        // get the dates depending on the type of invoice
        $period = $invoice->getInterval();

        $customer = $invoice->getCustomer();
        $cardCode = $customer->getCard() instanceof Cards ?
            $customer->getCard()->getCode() :
            '';

        // generate the first common part between the two records
        $partionRecord1 = [
            "110",// 11
            $invoice->getDateTimeDate()->format("d/m/Y"), // 10
            substr($invoice->getInvoiceNumber(), 5), // 20
            "TC",// 30
            $invoice->getDateTimeDate()->format("d/m/Y"), // 50
            substr($invoice->getInvoiceNumber(), 5), // 61
            $cardCode, // 130
            $customer->getId(), // 78
            "CC001", // 241
            $invoice->getAmount(), // 140
        ];

        // generate the second common part between the two records
        $partionRecord2 = [
            $invoice->getAmount(), // 930
            $invoice->getIva(), // 1001
            $period->start()->format("d/m/Y"), // 1020
            $period->end()->format("d/m/Y"), // 1030
            "FR" // 99999
        ];

        // generate the first record
        $record1 = array_merge(
            ["TES"], // 3
            $partionRecord1,
            [""], // 660
            [""], // 681
            $partionRecord2
        );

        // generate the second record
        $record2 = array_merge(
            ["RIG"], // 3
            $partionRecord1,
            ["40"], // 660
            [strtoupper($invoice->getTypeItalianTranslation())], // 681
            $partionRecord2
        );

        // return the two records combined
        return implode(";", $record1) . "\r\n" . implode(";", $record2);
    }
}

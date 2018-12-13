<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\InvoicesRepository;
use SharengoCore\Entity\Invoices;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Cards;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Partners;
use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Entity\BonusPackagePayment;
use SharengoCore\Service\SimpleLoggerService as Logger;
use SharengoCore\Entity\Repository\ExtraPaymentsRepository;
// Externals
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
     * @var ExtraPaymentsRepository
     */
    private $extraPaymentsRepository;

    /**
     * @param InvoicesRepository $invoicesRepository
     * @param DatatableServiceInterface $datatableService,
     * @param EntityRepository $invoicesRepository
     * @param Logger $logger
     * @param mixed $invoiceConfig
     * @param ExtraPaymentsService $extraPaymentsService
     */
    public function __construct(
        InvoicesRepository $invoicesRepository,
        DatatableServiceInterface $datatableService,
        EntityManager $entityManager,
        Logger $logger,
        $invoiceConfig,
        ExtraPaymentsRepository $extraPaymentsRepository
    ) {
        $this->invoicesRepository = $invoicesRepository;
        $this->datatableService = $datatableService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->templateVersion = $invoiceConfig['template_version'];
        $this->subscriptionAmount = $invoiceConfig['subscription_amount'];
        $this->ivaPercentage = $invoiceConfig['iva_percentage'];
        $this->extraPaymentsRepository = $extraPaymentsRepository;
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
     * @param Partners | null $partner
     * @return Invoices[]
     */
    public function getInvoicesByFleetJoinCustomers($fleet = null, Partners $partner = null)
    {
        return $this->invoicesRepository->findInvoicesByFleetJoinCustomers($fleet, $partner);
    }

    /**
     * @param \DateTime $date
     * @param Fleet | null $fleet
     * @return Invoices[]
     */
    public function getInvoicesByDateAndFleetJoinCustomers(\DateTime $date, $fleet = null, Partners $partner = null)
    {
        return $this->invoicesRepository->findInvoicesByDateAndFleetJoinCustomers($date, $fleet, $partner);
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

    public function createInvoicesForTrips($tripPayments, $writeToDb = true, $monthly = null)
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
                    $invoice = $this->prepareInvoiceForTrips($customer, $tripPaymentsForFleet, $monthly);
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
     * @param bool $monthly
     * @return Invoices
     */
    public function prepareInvoiceForTrips(Customers $customer, $tripPayments, $monthly = null)
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
            ],
            $monthly
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
                'f' => [
                    'name' => $invoice->getFleetName(),
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
     * @param integer $vatPercentage
     * @return mixed
     */
    public function calculateAmountsWithTaxesFromTotal($amount, $vatPercentage = null)
    {
        $amounts = [];

        // calculate amounts
        $iva = $this->ivaFromTotal($amount, $vatPercentage);
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
     * @param ExtraPayments $extraPayment
     * @return Invoices
     */
    public function prepareInvoiceForExtraOrPenalty(ExtraPayments $extraPayment) {
        if(is_null($extraPayment->getVat())) {
            $vatPercentage = $this->ivaPercentage;
        } else {
            $vatPercentage = $extraPayment->getVat()->getPercentage();
        }

        $reasons = $this->parseReasons($extraPayment->getReasons(), $vatPercentage);
        
        $amounts = [
            'sum' => $this->calculateAmountsWithTaxesFromTotal($extraPayment->getAmount(), $vatPercentage),
            'iva' => $vatPercentage
        ];

        return Invoices::createInvoiceForExtraOrPenalty(
            $extraPayment->getCustomer(),
            $extraPayment->getFleet(),
            $this->templateVersion,
            $reasons,
            $amounts
        );
    }

    /**
     * Removes iva from each row.
     * The rows are formatted as [[[description][amount]]]
     * Older rows have a different format but they do not need to be accounted
     * for because the invoices have already been generated.
     *
     * @param mixed[] $reasons
     * @param integer $vatPercentage
     * @return mixed[]
     */
    private function parseReasons($reasons, $vatPercentage = null)
    {
        $parsedReasons = [];

        foreach ($reasons as $key1 => $value1) {
            $grossAmount = $value1[count($value1)-1][0];
            $amount = $this->amountFromFormattedString($grossAmount);
            $amount = $this->parseDecimal($amount - $this->ivaFromTotal($amount, $vatPercentage)) . ' €';
            $parsedReasons[] = [[$value1[0][0]],[$amount]];
        }

        return $parsedReasons;
    }

    /**
     * Gets the iva (cents of euro) from the total (cents of euro) 
     *
     * @param integer $total
     * @param integer $vatPercentage
     * @return integer $iva
     */
    private function ivaFromTotal($total, $vatPercentage = null)
    {
        if(is_null($vatPercentage)) {
            $taxRate = $this->ivaPercentage / 100;
        } else {
            $taxRate = $vatPercentage / 100;
        }

        $priceWithoutTax = round($total / ( 1 + $taxRate));
        $iva = (integer) round($priceWithoutTax * $taxRate);

        return $iva;
    }

    /**
     * Removes € symbol and space and converts to integer amount
     *
     * @param string $formattedAmount
     * @return integer
     */
    private function amountFromFormattedString($formattedAmount)
    {
        return floor(floatval(substr($formattedAmount, 0, strlen($formattedAmount)-3)) * 100);
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
            $this->getVatCode($invoice), // 1001
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

    /**
     * For penaltyes we find the vat code, because the vat percentage can be different.
     *
     * @param Invoices $invoice
     * @return string
     */
    private function getVatCode(Invoices $invoice) {
        $result =  (string)$invoice->getIva();

        if($invoice->getType()==Invoices::TYPE_PENALTY) {
            $extraPayments = $this->extraPaymentsRepository->findExtraPaymentsByInvoice($invoice);
            if(!is_null($extraPayments[0]->getVat())) {
                $result = $extraPayments[0]->getVat()->getCode();
            }
        }

        return $result;
    }

    /**
     * Return the array for head invoice data.
     *
     * @param Invoices $invoice
     * @return array
     */
    private function getInvoiceHead(Invoices $invoice) {
        $result = array();

        $cardCode = $invoice->getCustomer()->getCard() instanceof Cards ?
            $invoice->getCustomer()->getCard()->getCode() :
            '';

        $result = [
            "TES",                                                     // 1
            "110",                                                     // 2   11
            $invoice->getDateTimeDate()->format("d/m/Y"),       // 3   10
            substr($invoice->getInvoiceNumber(), 5),             // 4   20
            "TC",                                                      // 5   30
            $invoice->getDateTimeDate()->format("d/m/Y"),       // 6   50
            substr($invoice->getInvoiceNumber(), 5),             // 7   61
            $cardCode,                                                 // 8     130
            $invoice->getCustomer()->getId(),                          // 9     78
            "CC001",                                                   // 10    241
            $invoice->getAmount(),                                     // 11    140
            "",                                                        // 12
            "",                                                        // 13
            $invoice->getAmount(),                                     // 14    930
            $this->getVatCode($invoice),                               // 15    1001
            $invoice->getInterval()->start()->format("d/m/Y"),         // 16    1020
            $invoice->getInterval()->end()->format("d/m/Y"),           // 17    1030
            "FR"                                                       // 18    99999
        ];

        return $result;
    }

    private function getInvoiceRows(Invoices $invoice) {
        $result = array();

        if($invoice->getType()==Invoices::TYPE_TRIP) {
            $tripPayments = $this->tripPaymentsRepository->findTripPaymentsByInvoice($invoice);

            foreach ($tripPayments as $tripPayment) {
                //Corsa: 4041230 Targa: EH24728 Inizio: 03-11-28 22:01:28 Viale Adriatico, Roma, RM Fine: 04-11-18 03:56:18 Via Nera, Roma, RM Durata: 350 min
                $description =sprintf("Corsa: %s Targa: %s Inizio: %s %s Fine: %s %s Durata: %d min",
                    $tripPayment->getTrip()->getId(),
                    $tripPayment->getTrip()->getCar()->getPlate(),
                    $tripPayment->getTrip()->getTimestampBeginning()->format("d/m/y h:m:s"),
                    $tripPayment->getTrip()->getAddressBeginning(),
                    $tripPayment->getTrip()->getTimestampEnd()->format("d/m/y h:m:s"),
                    $tripPayment->getTrip()->getAddressEnd(),
                    $tripPayment->getTrip()->getDurationMinutes()
                    );
                $row = $this->getInvoiceSingleRow($invoice, $tripPayment->getAmount(), $description);
                array_push($result, $row);
            }

        } else if($invoice->getType()==Invoices::TYPE_BONUS_PACKAGE) {

        } else if($invoice->getType()==Invoices::TYPE_FIRST_PAYMENT) {

        } else {
            //Invoices::PENALTY alias ExtraPayments
            $extraPayments = $this->extraPaymentsRepository->findExtraPaymentsByInvoice($invoice);

            foreach ($extraPayments as $extraPayment) {
                $row = $this->getInvoiceSingleRow($invoice, $extraPayment->getAmount(), $extraPayment->getReasons()[0]);
                array_push($result, $row);
            }
        }

        return $result;
    }

    private function getInvoiceSingleRow(Invoice $invoice, $amount, $description) {
        $result = array();

        $cardCode = $invoice->getCustomer()->getCard() instanceof Cards ?
            $invoice->getCustomer()->getCard()->getCode() :
            '';

        $result = [
            "RIG",                                                     // 1
            "110",                                                     // 2   11
            $invoice->getDateTimeDate()->format("d/m/Y"),       // 3   10
            substr($invoice->getInvoiceNumber(), 5),             // 4   20
            "TC",                                                      // 5   30
            $invoice->getDateTimeDate()->format("d/m/Y"),       // 6   50
            substr($invoice->getInvoiceNumber(), 5),             // 7   61
            $cardCode,                                                 // 8     130
            $invoice->getCustomer()->getId(),                          // 9     78
            "CC001",                                                   // 10    241
            $amount,                                                   // 11    140
            "",                                                        // 12
            "40",                                                        // 13
            $amount,                                                    // 14    930
            $this->getVatCode($invoice),                               // 15    1001
            $invoice->getInterval()->start()->format("d/m/Y"),         // 16    1020
            $invoice->getInterval()->end()->format("d/m/Y"),           // 17    1030
            "FR",                                                       // 18    99999
            $desription,                                                // 19
        ];


        return $result;
    }
}

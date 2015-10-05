<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use SharengoCore\Entity\Invoices;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Utils\Interval;
use Doctrine\ORM\EntityManager;
use Doctrine\Orm\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Invoices
 *
 * @ORM\Table(name="invoices")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\InvoicesRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Invoices
{

    const TYPE_FIRST_PAYMENT = 'FIRST_PAYMENT';

    const TYPE_TRIP = 'TRIP';

    const TYPE_PENALTY = 'PENALTY';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="invoices_id_seq", allocationSize=1, initialValue=0)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", nullable=false)
     */
    private $invoiceNumber;

    /**
     * @var \SharengoCore\Entity\Customers
     *
     * @ORM\ManyToOne(targetEntity="SharengoCore\Entity\Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_ts", type="datetime", nullable=false)
     */
    private $generatedTs;

    /**
     * @var array
     *
     * @ORM\Column(name="content", type="json_array", nullable=false)
     */
    private $content = [];

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="invoice_date", type="integer", nullable=false)
     */
    private $invoiceDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="iva", type="integer", nullable=false)
     */
    private $iva;

    /**
     * @var \SharengoCore\Entity\Fleet
     *
     * @ORM\ManyToOne(targetEntity="SharengoCore\Entity\Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $fleet;

    /**
     * @param Customers $customer
     * @param integer $version
     * @param string $type
     * @param integer $date
     * @param array $amounts
     * @param Fleet|null $fleet
     * @return Invoices
     */
    private function __construct(
        Customers $customer,
        $version,
        $type,
        $date,
        $amounts,
        $fleet = null
    ) {
        $this->generatedTs = date_create(date('Y-m-d H:i:s'));
        $this->customer = $customer;
        $this->version = $version;
        $this->type = $type;
        $this->invoiceDate = $date;
        $this->amount = $amounts['sum']['grand_total_cents'];
        $this->iva = $amounts['iva'];

        if ($fleet instanceof Fleet) {
            $this->fleet = $fleet;
        } else {
            $this->fleet = $customer->getFleet();
        }

        $this->content = [
            'invoice_date' => $this->getInvoiceDate(),
            'amounts' => $amounts['sum'],
            'iva' => $amounts['iva'],
            'customer' => [
                'name' => $customer->getName(),
                'surname' => $customer->getSurname(),
                'email' => $customer->getEmail(),
                'address' => $customer->getAddress(),
                'town' => $customer->getTown(),
                'province' => $customer->getProvince(),
                'country' => $customer->getCountry(),
                'zip_code' => $customer->getZipCode(),
                'cf' => $customer->getTaxCode(),
                'piva' => $customer->getVat()
            ],
            'type' => $type,
            'template_version' => $version,
            'header' => $fleet->getInvoiceHeader()
        ];

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist($e)
    {
        $entityManager = $e->getEntityManager();
        $entityManager->beginTransaction();
        $this->invoiceNumber = $this->retrieveNewInvoiceNumber($entityManager, $e->getEntity());
    }

    private function retrieveNewInvoiceNumber(EntityManager $entityManager, Invoices $invoice)
    {
        $newInvoiceNumber = $this->getNewInvoiceNumber($entityManager, $invoice);

        return $this->formatNewInvoiceNumber($invoice, $newInvoiceNumber);
    }

    /**
     * we use the invoice_number table in the database to keep track of the
     * sequence of the invoice numbers
     *
     * @param EntityManager $entityManager
     * @param Invoices $invoice
     * @return string|null
     */
    private function getNewInvoiceNumber(EntityManager $entityManager, Invoices $invoice)
    {
        // we look if there is already an invoice number for the same year and fleet
        // if that is the case we increment the counter and return the new value
        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addScalarResult('number', 'number');
        $newInvoiceNumberQuery = $entityManager->createNativeQuery(
            'UPDATE invoice_number SET number = number + 1 '.
            'WHERE year = :year '.
            'AND fleet_id = :fleet_id '.
            'RETURNING number;',
            $resultSetMapping
        );

        $invoiceYear = $invoice->getDateTimeDate()->format('Y');
        $fleetId = $invoice->getFleetId();
        $newInvoiceNumberQuery->setParameter('year', $invoiceYear);
        $newInvoiceNumberQuery->setParameter('fleet_id', $fleetId);

        $newInvoiceNumber =  $newInvoiceNumberQuery->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        if (is_null($newInvoiceNumber)) {
            // if no invoice was generated before for the same year and fleet
            // we insert a new line starting from 1
            $newInvoiceNumberQuery = $entityManager->createNativeQuery(
                'INSERT INTO invoice_number (year, fleet_id, number) VALUES '.
                '(:year, :fleet_id, 1) '.
                'RETURNING number;',
                $resultSetMapping
            );
            $newInvoiceNumberQuery->setParameter('year', $invoiceYear);
            $newInvoiceNumberQuery->setParameter('fleet_id', $fleetId);
            $newInvoiceNumber =  $newInvoiceNumberQuery->getSingleScalarResult();
        }

        return $newInvoiceNumber;
    }

    /**
     * @param Invoices $invoice
     * @param int $newInvoiceNumber
     */
    private function formatNewInvoiceNumber(Invoices $invoice, $newInvoiceNumber)
    {
        return date_create()->format('Y').
            '/'.
            $invoice->getFleetIntCode().
            sprintf("%'.08d", $newInvoiceNumber);
    }

    /**
     * @ORM\PostPersist
     */
    public function postPersist($e)
    {
        $em = $e->getEntityManager();
        $em->commit();
    }

    /**
     * @param Customers $customer
     * @param integer $version
     * @param mixed $amounts
     * @return Invoice
     */
    public static function createInvoiceForFirstPayment(
        Customers $customer,
        $version,
        $amounts
    ) {
        $invoice = new Invoices(
            $customer,
            $version,
            self::TYPE_FIRST_PAYMENT,
            intval(date("Ymd")),
            $amounts
        );

        $invoice->setContentBody([
            'greeting_message' => '<p>Nella pagina successiva troverà i dettagli del pagamento per l\'iscrizione al servizio<br>' .
                'L\'importo totale della fattura è di EUR ' .
                $amounts['sum']['grand_total'] .
                '</p>',
            'contents' => [
                'header' => [
                    'Descrizione',
                    'Imponibile'
                ],
                'body' => [
                    [
                        ['Pagamento iscrizione al servizio'],
                        [$amounts['sum']['total'] . ' €']
                    ]
                ],
                'body-format' => [
                    'alignment' => [
                        'left',
                        'right'
                    ]
                ]
            ]
        ]);

        return $invoice;
    }

    /**
     * Creates an invoice for a set of trips.
     *
     * It's supposed all of them have been payed on the same day
     *
     * @param Customers $customer
     * @param TripPayments[] $tripPayments
     * @param integer $version
     * @param mixed $amounts
     * @return Invoices
     */
    public static function createInvoiceForTrips(
        Customers $customer,
        $tripPayments,
        $version,
        $amounts
    ) {
        $invoice = new Invoices(
            $customer,
            $version,
            self::TYPE_TRIP,
            intval($tripPayments[0]->getPayedSuccessfullyAt()->format("Ymd")), // it's supposed all trips have been payed on the same day
            $amounts
        );

        $body = [];

        foreach ($tripPayments as $key => $tripPayment) {
            $trip = $tripPayment->getTrip();
            /**
             * Changing the order, structure or content of the following
             * may interfere with $this->getInterval() function!
             * Test by running "export registries -d -c" from console
             */
            array_push($body, [
                [$trip->getId()],
                ["Inizio: " . $trip->getTimestampBeginning()->format("d-m-Y H:i:s"),
                    "Fine: " . $trip->getTimestampEnd()->format("d-m-Y H:i:s")],
                ["Da: " . $trip->getAddressBeginning(),
                    "A: " . $trip->getAddressEnd()],
                [$tripPayment->getTripMinutes() . ' (min)'],
                [$trip->getCar()->getPlate()],
                [$amounts['rows'][$key] . ' €']
            ]);
        }

        $invoice->setContentBody([
            'greeting_message' => '',
            'contents' => [
                'header' => [
                    'ID',
                    'Data',
                    'Partenza / Arrivo',
                    'Durata',
                    'Targa',
                    'Totale'
                ],
                'body' => $body,
                'body-format' => [
                    'alignment' => [
                        'left',
                        'left',
                        'left',
                        'left',
                        'left',
                        'right'
                    ]
                ]
            ]
        ]);

        return $invoice;
    }

    /**
     * @param Customers $customer
     * @param Fleet $fleet
     * @param int $version template version
     * @param string $reason
     * @param array $amounts with fields grand_total_cents, grand_total, total, iva
     */
    public function createInvoiceForExtraOrPenalty(
        Customers $customer,
        Fleet $fleet,
        $version,
        $reason,
        $amounts
    ) {
        $invoice = new Invoices(
            $customer,
            $version,
            self::TYPE_PENALTY,
            intval(date("Ymd")),
            $amounts,
            $fleet
        );

        $invoice->setContentBody([
            'greeting_message' => '<p>Nella pagina successiva troverà i dettagli del pagamento<br>' .
                'L\'importo totale della fattura è di EUR ' .
                $amounts['sum']['grand_total'] .
                '</p>',
            'contents' => [
                'header' => [
                    'Descrizione',
                    'Imponibile'
                ],
                'body' => [
                    [
                        [$reason],
                        [$amounts['sum']['total'] . ' €']
                    ]
                ],
                'body-format' => [
                    'alignment' => [
                        'left',
                        'right'
                    ]
                ]
            ]
        ]);

        return $invoice;
    }

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $customer = $this->getCustomer();
        if ($customer != null) {
            $customer = $customer->toArray($hydrator);
        }

        $extractedInvoice = $hydrator->extract($this);
        $extractedInvoice['customer'] = $customer;

        return $extractedInvoice;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \SharengoCore\Entity\Customers
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return \DateTime
     */
    public function getGeneratedTs()
    {
        return $this->generatedTs;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @var array $body
     * @return Invoices
     */
    public function setContentBody($body)
    {
        $this->content['body'] = $body;

        return $this;
    }

    /**
     * @return integer
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getTypeItalianTranslation()
    {
        switch ($this->getType()) {
            case 'FIRST_PAYMENT':
                return 'Iscrizione';
            case 'TRIP':
                return 'Corse';
            case 'PENALTY':
                return 'Sanzione';
        }
        return '';
    }

    /**
     * @return integer
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @return integer
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * @return \DateTime the value of invoiceDate converted to \DateTime
     */
    public function getDateTimeDate()
    {
        $date = $this->getInvoiceDate();
        $date = ($date % 100) . "/" . (floor(($date % 10000) / 100)) . "/" . floor($date / 10000);
        return date_create_from_format("d/m/Y", $date);
    }

    /**
     * @return Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * @return string
     */
    public function getFleetIntCode()
    {
        return $this->fleet->getIntCode();
    }

    /**
     * @return int
     */
    public function getFleetId()
    {
        return $this->fleet->getId();
    }

    /**
     * @return Interval
     */
    public function getInterval()
    {
        /*
         * For invoices of type "TRIP" the interval is defined as:
         * - "start" the date of the beginning of the trip for
         *   the first tripPayment of the invoice
         * - "end" the date of the end of the trip for
         *   the last tripPayment of the invoice
         */
        if ($this->getType() == $this::TYPE_TRIP) {
            // Get the body with all the invoice rows
            $body = $this->getContent()['body']['contents']['body'];
            // Generate two starting dates to start comparing against
            $startDate = date_create_from_format("d-m-Y H:i:s", substr($body[0][1][0], 8));
            $endDate = date_create_from_format("d-m-Y H:i:s", substr($body[0][1][1], 6));
            // Compare all dates to find highest and lowest
            foreach ($body as $times) {
                $start = date_create_from_format("d-m-Y H:i:s", substr($times[1][0], 8));
                $end = date_create_from_format("d-m-Y H:i:s", substr($times[1][1], 6));
                // Compare start dates
                if ($start < $startDate) {
                    $startDate = $start;
                }
                // Compare end dates
                if ($end > $endDate) {
                    $endDate = $end;
                }
            }
            return new Interval($startDate, $endDate);

        /*
         * For invoices of type "FIRST_PAYMENT" and "PENALTY",
         * the interval is defined as:
         * - "start" the date of the invoice
         * - "end" the date of the invoice
         */
        } else {
            return new Interval($this->getDateTimeDate(), $this->getDateTimeDate());
        }
    }
}

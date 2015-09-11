<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use SharengoCore\Entity\Invoices;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;

/**
 * Invoices
 *
 * @ORM\Table(name="invoices")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\InvoicesRepository")
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
     * @var string
     *
     * @ORM\Column(name="invoice_number", type="string", nullable=false)
     */
    private $invoiceNumber;

    /**
     * @param Customers $customer
     * @param integer $version
     * @param string $type
     * @param integer $date
     * @param array $amounts needed fields grand_total_cents, grand_total, total, iva
     * @return Invoices
     */
    private function __construct(Customers $customer, $version, $type, $date, $amounts)
    {
        $this->generatedTs = date_create(date('Y-m-d H:i:s'));

        $this->customer = $customer;
        $this->version = $version;
        $this->type = $type;
        $this->invoiceDate = $date;
        $this->amount = $amounts['grand_total_cents'];

        $this->content = [
            'invoice_date' => $date,
            'amounts' => $amounts,
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
            'template_version' => $version
        ];

        return $this;
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
                $amounts['grand_total'] .
                '</p>',
            'contents' => [
                'header' => [
                    'Descrizione',
                    'Imponibile'
                ],
                'body' => [
                    [
                        ['Pagamento iscrizione al servizio'],
                        [$amounts['total'] . ' €']
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
            $amounts['sum']
        );

        $body = [];

        foreach ($tripPayments as $key => $tripPayment) {
            $trip = $tripPayment->getTrip();
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

        $body = [
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
        ];

        $invoice->setContentBody($body);

        return $invoice;
    }

    /**
     * @param Customers $customer
     * @param int $version template version
     * @param string $reason
     * @param array $amounts with fields grand_total_cents, grand_total, total, iva
     */
    public function createInvoiceForExtraOrPenalty(
        Customers $customer,
        $version,
        $reason,
        $amounts
    ) {
        $invoice = new Invoices(
            $customer,
            $version,
            self::TYPE_PENALTY,
            intval(date("Ymd")),
            $amounts
        );

        $invoice->setContentBody([
            'greeting_message' => '<p>Nella pagina successiva troverà i dettagli del pagamento<br>' .
                'L\'importo totale della fattura è di EUR ' .
                $amounts['grand_total'] .
                '</p>',
            'contents' => [
                'header' => [
                    'Descrizione',
                    'Imponibile'
                ],
                'body' => [
                    [
                        [$reason],
                        [$amounts['total'] . ' €']
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
}

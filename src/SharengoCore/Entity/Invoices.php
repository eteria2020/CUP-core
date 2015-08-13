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
     * @var mixed
     *
     * @ORM\Column(name="content", type="json_array", nullable=false)
     */
    private $content;

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

    public function __construct()
    {
        $this->generatedTs = date_create(date('Y-m-d H:i:s'));
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
     * @param string $type
     * @param \DateTime $date
     * @param mixed $amounts
     * @return Invoices
     */
    public static function createBasicInvoice(Customers $customer, $version, $type, $date, $amounts)
    {
        $invoice = new Invoices();

        $invoice->setCustomer($customer)
            ->setVersion($version)
            ->setType($type)
            ->setInvoiceDate($date)
            ->setAmount($amounts['grand_total_cents']);

        $content = [
            'invoice_date' => $invoice->getInvoiceDate(),
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
            'type' => $invoice->getType(),
            'template_version' => $invoice->getVersion()
        ];

        return $invoice->setContent($content);
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
        $invoice = Invoices::createBasicInvoice(
            $customer,
            $version,
            self::TYPE_FIRST_PAYMENT,
            intval(date("Ymd")),
            $amounts
        );

        $content = $invoice->getContent();

        $content['body'] = [
            'greeting_message' => '<p>Nella pagina successiva troverà i dettagli del pagamento per l\'iscrizione al servizio<br>' .
                'L\'importo totale della fattura è di EUR ' .
                $amounts['grand_total'] .
                '</p>',
            'description' => 'Pagamento iscrizione al servizio',
            'contents' => [
                'header' => [
                    'Descrizione',
                    'Imponibile'
                ],
                'body' => [
                    [
                        'Pagamento iscrizione al servizio',
                        $amounts['total'] . ' €'
                    ]
                ],
                'body-format' => [
                    'alignment' => [
                        'left',
                        'right'
                    ]
                ]
            ]
        ];

        $invoice->setContent($content);

        return $invoice;
    }

    /**
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
        $invoice = Invoices::createBasicInvoice(
            $customer,
            $version,
            self::TYPE_TRIP,
            intval($tripPayments[0]->getCreatedAt()->format("Ymd")),
            $amounts
        );

        $content = $invoice->getContent();

        $body = [];

        foreach ($tripPayments as $tripPayment) {
            $trip = $tripPayment->getTrip();
            array_push($body, [
                $trip->getTimestampBeginning()->format("Y-m-d H:i:s"),
                $tripPayment->getTripMinutes(),
                $trip->getParkSeconds(),
                $trip->getKmEnd() - $trip->getKmBeginning(),
                $tripPayment->getTotalCost()
            ]);
        }

        $content['body'] = [
            'greeting_message' => '',
            'description' => '',
            'contents' => [
                'header' => [
                    'Ora inizio',
                    'Durata',
                    'Tempo in sosta',
                    'Distanza',
                    'Totale'
                ],
                'body' => $body,
                'body-format' => [
                    'alignment' => [
                        'left',
                        'left',
                        'left',
                        'left',
                        'right'
                    ]
                ]
            ]
        ];

        $invoice->setContent($content);

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
     * @var \SharengoCore\Entity\Customers $customer
     * @return Invoices
     */
    public function setCustomer(Customers $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getGeneratedTs()
    {
        return $this->generatedTs;
    }

    /**
     * @var \DateTime $generatedTs
     * @return Invoices
     */
    public function setGeneratedTs($generatedTs)
    {
        $this->generatedTs = $generatedTs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @var mixed $content
     * @return Invoices
     */
    public function setContent($content)
    {
        $this->content = $content;
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
     * @var integer $version
     * @return Invoices
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @var string $type
     * @return Invoices
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return integer
     */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * @var integer $invoiceDate
     * @return Invoices
     */
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;
        return $this;
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @var integer $amount
     * @return Invoices
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }

    /**
     * @var string $invoiceNumber
     * @return Invoices
     */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }
}

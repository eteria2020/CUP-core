<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use SharengoCore\Entity\Invoices;

/**
 * Invoices
 *
 * @ORM\Table(name="invoices")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\InvoicesRepository")
 */
class Invoices
{
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
     * @var string
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
     * @ORM\Column(name="invoice_type", type="string", nullable=false)
     */
    private $invoiceType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="emitted_ts", type="datetime", nullable=false)
     */
    private $emittedTs;

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
     * @param \SharengoCore\Entity\Customers $customer
     * @param integer $version
     * @return Invoice
     */
    public static function createInvoiceForFirstPayment($customer, $version)
    {
        $invoice = new Invoice();

        $content = [
            'name' => $customer->getName(),
            'surname' => $customer->getSurname(),
            'invoice_number' => 'invoice_number_test',
            'emitted_ts' => date_create(date('Y-m-d H:i:s')), // how to retrieve for paypal transactions
            'amount' => 1000,
            'card' => $customer->getCard()->getCode(),
            'email' => $customer->getEmail(),
            'address' => $customer->getAddress(),
            'cf' => $customer->getTaxCode(),
            'piva' => $customer->getVat()
        ];

        $invoice->setCustomer($customer)
            ->setContent($content)
            ->setVersion($version)
            ->setInvoiceType('FIRST_PAYMENT')
            ->setEmittedTs($content['emitted_ts'])
            ->setAmount(1000)
            ->setInvoiceNumber('invoice_number_test'); // might create autogenerated sequence on db

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
    public function getInvoiceType()
    {
        return $this->invoiceType;
    }

    /**
     * @var string $invoiceType
     * @return Invoices
     */
    public function setInvoiceType($invoiceType)
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEmittedTs()
    {
        return $this->emittedTs;
    }

    /**
     * @var \DateTime $emittedTs
     * @return Invoices
     */
    public function setEmittedTs($emittedTs)
    {
        $this->emittedTs = $emittedTs;
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

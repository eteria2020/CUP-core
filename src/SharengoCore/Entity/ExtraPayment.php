<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripPayments
 *
 * @ORM\Table(name="extra_payments")
 * @ORM\Entity
 */
class ExtraPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="extra_payments_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var Fleet
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $fleet;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", nullable=false)
     */
    private $paymentType;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", nullable=false)
     */
    private $reason;

    /**
     * @var Invoices
     *
     * @ORM\ManyToOne(targetEntity="Invoices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $invoice;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="invoiced_at", type="datetime", nullable=true)
     */
    private $invoicedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="invoice_able", type="boolean", nullable=false, options={"default" = TRUE})
     */
    private $invoiceAble;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="generated_ts", type="datetime", nullable = false)
     */
    private $generatedTs;

    /**
     * @param Customer $customer
     * @param Fleet $fleet
     * @param integer $amount
     * @param string $paymentType
     * @param string $reason
     * @return ExtraPayment
     */
    public function __construct(
        Customers $customer,
        Fleet $fleet,
        $amount,
        $paymentType,
        $reason
    ) {
        $this->customer = $customer;
        $this->fleet = $fleet;
        $this->amount = $amount;
        $this->paymentType = $paymentType;
        $this->reason = $reason;
        $this->invoiceAble = true;
        $this->generatedTs = date_create();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Customers
     */
    public function getCustomer()
    {
        return $this->customer;
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
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Invoices $invoice
     * @return self
     */
    public function associateInvoice(Invoices $invoice)
    {
        $this->invoice = $invoice;
        $this->invoicedAt = date_create();

        return $invoice;
    }

    /**
     * @return Invoices
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}

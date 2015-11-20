<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

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
     * @var array
     *
     * @ORM\Column(name="reasons", type="json_array", nullable=false)
     */
    private $reasons;

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
     * @var Transactions
     *
     * @ORM\OneToOne(targetEntity="\Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transaction;

    /**
     * @param Customer $customer
     * @param Fleet $fleet
     * @param Transactions $transaction
     * @param integer $amount
     * @param string $paymentType
     * @param array $reasons
     * @return ExtraPayment
     */
    public function __construct(
        Customers $customer,
        Fleet $fleet,
        Transactions $transaction,
        $amount,
        $paymentType,
        $reasons
    ) {
        $this->customer = $customer;
        $this->fleet = $fleet;
        $this->transaction = $transaction;
        $this->amount = $amount;
        $this->paymentType = $paymentType;
        $this->reasons = $reasons;
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

<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use SharengoCore\Exception\AlreadySetFirstExtraTryTsException;


/**
 * ExtraPayments
 *
 * @ORM\Table(name="extra_payments")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ExtraPaymentsRepository")
 */
class ExtraPayments
{
    const STATUS_TO_BE_PAYED = 'to_be_payed';
    const STATUS_PAYED_CORRECTLY = 'payed_correctly';
    const STATUS_WRONG_PAYMENT = 'wrong_payment';
    const STATUS_INVOICED = 'invoiced';
    
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
     * @var string can have values
     *      - to_be_payed (default)
     *      - payed_correctly
     *      - wrong_payment
     *      - invoiced
     *
     * @ORM\Column(name="status", type="string", nullable=false, options={"default" = "to_be_payed"})
     */
    private $status;
    
    /**
     * @var ExtraPaymentTries[]
     *
     * @ORM\OneToMany(targetEntity="ExtraPaymentTries", mappedBy="extraPayment")
     * @ORM\OrderBy({"ts" = "ASC"})
     */
    private $extraPaymentTries;
    
    /**
     * Holds the timestamp of the first extraPaymentTries associated with this
     * extraPayments. If a user's credit card is removed and the extraPayments's
     * status is set to to_be_payed, this value shall be set to NULL untill a
     * new extraPaymentTries is created
     *
     * @var DateTime
     *
     * @ORM\Column(name="first_extra_try_ts", type="datetime", nullable=true)
     */
    private $firstExtraTryTs;

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
        //Transactions $transaction,
        $transaction,
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
        $this->status = self::STATUS_TO_BE_PAYED;
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
    public function getReasons()
    {
        return $this->reasons;
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
    
    
    public function getGeneratedTs(){
        return $this->generatedTs;
    }
    
    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return ExtraPayments
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isWrongExtra()
    {
        return $this->status === self::STATUS_WRONG_PAYMENT;
    }
    
    /**
     * @return ExtraPaymentTries[]
     */
    public function getExtraPaymentTries()
    {
        return $this->extraPaymentTries;
    }
    
    /**
     * @return string
     */
    public function getFormattedTotalCost()
    {
        return floor($this->amount / 100) .
            ',' .
            ($this->amount % 100 < 10 ? '0' : '') .
            $this->amount % 100 .
            '€';
    }
    
    /**
     * @return Transactions
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param Transactions
     */
    public function setTransaction(Transactions $transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isFirstExtraTryTsSet()
    {
        return $this->firstExtraTryTs !== null;
    }
    
    /**
     * Sets the value of firstExtraTryTs. If the value of firstExtraTryTs
     * is already set, throws exception AlreadySetFirstPaymentTryTsException.
     * To prevent this, use method isFirstPaymentTryTsSet()
     *
     * @param DateTime $firstExtraTryTs
     * @return ExtraPayments
     * @throws AlreadySetFirstExtraTryTsException
     */
    public function setFirstExtraTryTs($firstExtraTryTs)
    {
        if ($this->isFirstExtraTryTsSet()) {
            throw new AlreadySetFirstExtraTryTsException();
        }
        $this->firstExtraTryTs = $firstExtraTryTs;
        return $this;
    }
    
    /**
     * @return ExtraPayments
     */
    public function setWrongExtra()
    {
        return $this->setStatus(self::STATUS_WRONG_PAYMENT);
    }
    
    /**
     * @return ExtraPayments
     */
    public function setPayedCorrectly()
    {
        return $this->setStatus(self::STATUS_PAYED_CORRECTLY);
    }

}

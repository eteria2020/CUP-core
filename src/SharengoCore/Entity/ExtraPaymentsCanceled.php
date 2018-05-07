<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExtraPaymentsCanceled
 *
 * @ORM\Table(name="extra_payments_canceled")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ExtraPaymentsCanceledRepository")
 */
class ExtraPaymentsCanceled
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="extra_payments_canceled_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_ts", type="datetime", nullable=false)
     */
    private $generatedTs;

    /**
     * @var string
     *
     * @ORM\Column(name="reasons", type="string", nullable=false)
     */
    private $reasons;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", nullable=false)
     */
    private $paymentType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_extra_try_ts", type="datetime", nullable=true)
     */
    private $firstExtraTryTs;

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
     * @var \Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $webuser;

    /**
     * @var \Transactions
     *
     * @ORM\ManyToOne(targetEntity="Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transaction;
    
    /**
     * @param TripPayments $extraPayment
     * @param Webuser $webuser
     * @return ExtraPaymentsCanceled
     */
    public function __construct(ExtraPayments $extraPayment, Webuser $webuser)
    {
        $this->insertedTs = date_create();
        $this->webuser = $webuser;
        $this->customer = $extraPayment->getCustomer();
        $this->amount = $extraPayment->getAmount();
        $this->fleet = $extraPayment->getFleet();
        $this->generatedTs = $extraPayment->getGeneratedTs();
        $this->transaction = $extraPayment->getTransaction();
        $this->reasons = $extraPayment->getReasons();
        $this->paymentType = $extraPayment->getPaymentType();
        $this->firstExtraTryTs = $extraPayment->getFirstExtraTryTs();
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
     * 
     * @return \SharengoCore\Entity\Customers
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
    
    public function getGeneratedTs(){
        return $this->generatedTs;
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
     * @return Date
     */
    public function getFirstExtraTryTs() {
         return $this->firstExtraTryTs;
    }
    
    /**
     * @return Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * @return Webuser
     */
    public function getInsertedTs()
    {
        return $this->insertedTs;
    }
}


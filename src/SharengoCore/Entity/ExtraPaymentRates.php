<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExtraPaymentRates
 *
 * @ORM\Table(name="extra_payment_rates")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ExtraPaymentRatesRepository")
 */
class ExtraPaymentRates
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="extra_payment_rates_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetime", nullable=false)
     */
    private $insertTs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="debit_ts", type="datetime", nullable=false)
     */
    private $debitTs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="payable", type="boolean", nullable=false)
     */
    private $payable;

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
     * @var \ExtraPayments
     *
     * @ORM\ManyToOne(targetEntity="ExtraPayments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extra_payment_father_id", referencedColumnName="id")
     * })
     */
    private $extraPaymentFather;
    
    /**
     * @var \ExtraPayments
     *
     * @ORM\ManyToOne(targetEntity="ExtraPayments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extra_payment_id", referencedColumnName="id")
     * })
     */
    private $extraPayment;
    
    
    public function __construct(
        Customers $customer,
        ExtraPayments $extraPaymentFather,
        $amount,
        $date
    ) {
        $this->customer = $customer;
        $this->amount = $amount;
        $this->insertTs = date_create();
        $this->debitTs = $date;
        $this->extraPaymentFather = $extraPaymentFather;
        $this->payable = true;
    }
    
    
    function getId() {
        return $this->id;
    }

    function getAmount() {
        return $this->amount;
    }

    function getInsertTs() {
        return $this->insertTs;
    }

    function getDebitTs() {
        return $this->debitTs;
    }

    function getPayable() {
        return $this->payable;
    }
    
    function setPayable($payable) {
        $this->payable = $payable;
    }

    function getCustomer() {
        return $this->customer;
    }

    function getExtraPayment() {
        return $this->extraPayment;
    }

    function getExtraPaymentFather() {
        return $this->extraPaymentFather;
    }
    
    function setExtraPayment(ExtraPayments $extraPayment) {
        $this->extraPayment = $extraPayment;
    }

}


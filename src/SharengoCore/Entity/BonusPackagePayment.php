<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Invoices;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonusPackagePayment
 *
 * @ORM\Table(name="bonus_package_payments")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\BonusPackagePaymentRepository")
 */
class BonusPackagePayment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="bonus_package_payments_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var \CustomersBonus
     *
     * @ORM\OneToOne(targetEntity="CustomersBonus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bonus_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $bonus;

    /**
     * @var \CustomersBonusPackages
     *
     * @ORM\ManyToOne(targetEntity="CustomersBonusPackages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $package;

    /**
     * @var \Fleet
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $fleet;

    /**
     * @var Transaction
     *
     * @ORM\OneToOne(targetEntity="\Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $transaction;

    /**
     * @var Invoices
     *
     * @ORM\ManyToOne(targetEntity="\SharengoCore\Entity\Invoices")
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
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    public function __construct(
        Customers $customer,
        CustomersBonus $bonus,
        CustomersBonusPackages $package,
        Transactions $transaction
    ) {
        $this->setCustomer($customer);
        $this->setBonus($bonus);
        $this->setPackage($package);
        $this->setFleet($customer->getFleet());
        $this->setTransaction($transaction);
        $this->setAmount($package->getCost());
        $this->setInsertedTs(date_create());
    }

    /**
     * @return integer
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
     * @param Customers $customer
     *
     * @return self
     */
    public function setCustomer(Customers $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return CustomersBonus
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * @param CustomersBonus $bonus
     * @return self
     */
    public function setBonus(CustomersBonus $bonus)
    {
        $this->bonus = $bonus;

        return $this;
    }

    /**
     * @return CustomersBonusPackages
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param CustomersBonusPackages
     *
     * @return self
     */
    public function setPackage(CustomersBonusPackages $package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @return Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * @param Fleet $fleet
     *
     * @return self
     */
    public function setFleet(Fleet $fleet)
    {
        $this->paymentFleet = $fleet;

        return $this;
    }

    /**
     *
     * @param Transactions $transaction
     *
     * @return self
     */
    public function setTransaction(Transactions $transaction)
    {
        $this->transaction = $transaction;

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
     * @param integer $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param \DateTime $insertedTs
     *
     * @return self
     */
    public function setInsertedTs($insertedTs)
    {
        $this->insertedTs = $insertedTs;

        return $this;
    }

    /**
     * @param Invoices $invoice
     */
    public function associateInvoice(Invoices $invoice)
    {
        $this->invoice = $invoice;
        $this->invoicedAt = $invoice->getGeneratedTs();
    }
}

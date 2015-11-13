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
 * @ORM\Entity
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
        $this->customer = $customer;
        $this->bonus = $bonus;
        $this->package = $package;
        $this->fleet = $customer->getFleet();
        $this->transaction = $transaction;
        $this->amount = $package->getCost();
        $this->insertedTs = date_create();
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
     * @return CustomersBonus
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * @return CustomersBonusPackages
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
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

<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubscriptionPayment
 *
 * @ORM\Table(name="subscription_payments")
 * @ORM\Entity(readOnly=true)
 */
class SubscriptionPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="subscription_payments_id_seq", allocationSize=1, initialValue=1)
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
     * @var \Fleet
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $fleet;

    /**
     * @var Transactions
     *
     * @ORM\OneToOne(targetEntity="\Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $transaction;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetime", nullable = false)
     */
    private $insertTs;

    public function __construct(
        Customers $customer,
        Transactions $transaction
    ) {
        $this->customer = $customer;
        $this->fleet = $customer->getFleet();
        $this->transaction = $transaction;
        $this->amount = $transaction->getAmount();
        $this->insertTs = date_create();
    }
}

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
     * @param Customer $customer
     * @param integer $amount
     * @param string $paymentType
     * @param string $reason
     * @return ExtraPayment
     */
    public function __construct(
        Customers $customer,
        $amount,
        $paymentType,
        $reason
    ) {
        $this->customer = $customer;
        $this->amount = $amount;
        $this->paymentType = $paymentType;
        $this->reason = $reason;
    }
}

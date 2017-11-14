<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OldCustomerDiscounts
 *
 * @ORM\Table(name="old_customer_discounts")
 * @ORM\Entity
 */
class OldCustomerDiscount
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="old_customer_discounts_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers", inversedBy="oldDiscounts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var integer
     *
     * @ORM\Column(name="discount", type="integer", nullable=false)
     */
    private $discount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="obsolete_from", type="datetime", nullable=false)
     */
    private $obsoleteFrom;

    public function __construct(
        Customers $customer,
        $discount,
        $obsoleteFrom
    ) {
        $this->customer = $customer;
        $this->discount = $discount;
        $this->obsoleteFrom = $obsoleteFrom;
    }
}

<?php

namespace SharengoCore\Entity;

use SharengoCore\Entity\Customers;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiscountStatus
 *
 * @ORM\Table(name="discount_state")
 * @ORM\Entity
 */
class DiscountStatus
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="discount_state_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Customers
     *
     * @ORM\OneToOne(targetEntity="Customers", inversedBy="discountStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="discount_state", type="string", nullable=false)
     */
    private $discountState;

    public function __construct(Customers $customer, $status)
    {
        $this->customer = $customer;
        $this->discountState = $status;
    }

    public function status()
    {
        return $this->discountState;
    }

    public function updateStatus($status)
    {
        $this->discountState = $status;

        return $this;
    }
}

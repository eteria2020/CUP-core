<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="customers_bonus")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CustomersBonusRepository")
 */
class CustomersBonus
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="int", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customersbonus_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetime", nullable=false)
     */
    private $insertTs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_ts", type="datetime", nullable=false)
     */
    private $updateTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     */
    private $total;

    /**
     * @var integer
     *
     * @ORM\Column(name="residual", type="integer", nullable=false)
     */
    private $residual;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type = 'promo';

    /**
     * @var string
     *
     * @ORM\Column(name="operator", type="string", length=100, nullable=true)
     */
    private $operator;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration_days", type="integer", nullable=true)
     */
    private $durationDays;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    private $validTo;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

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
     * @var \Webuser
     *
     * @ORM\ManyToOne(targetEntity="\Application\Entity\Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $webuser;


}

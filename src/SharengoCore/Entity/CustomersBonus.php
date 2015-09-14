<?php

namespace SharengoCore\Entity;

use SharengoCore\Exception\NonPositiveIntegerException;

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
     * @ORM\Column(name="id", type="integer", nullable=false)
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
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $webuser;

    /**
     * @var \PromoCodes
     *
     * @ORM\ManyToOne(targetEntity="PromoCodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="promocode_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $promocode;


    public static function createFromPromoCode(PromoCodes $promoCode)
    {
        $promoCodeDetails = $promoCode->getPromocodesinfo();

        $me = new CustomersBonus();
        $me->setInsertTs(new \DateTime());
        $me->setUpdateTs($me->getInsertTs());
        $me->setTotal($promoCodeDetails->getMinutes());
        $me->setResidual($me->getTotal());
        $me->setValidFrom($me->getInsertTs());
        $me->setValidTo($promoCodeDetails->getValidTo());
        $me->setDescription($promoCode->getDescription());
        $me->setPromoCode($promoCode);

        return $me;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return CustomersBonus
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set insertTs
     *
     * @param \DateTime $insertTs
     *
     * @return CustomersBonus
     */
    public function setInsertTs($insertTs)
    {
        $this->insertTs = $insertTs;

        return $this;
    }

    /**
     * Get insertTs
     *
     * @return \DateTime
     */
    public function getInsertTs()
    {
        return $this->insertTs;
    }

    /**
     * Set updateTs
     *
     * @param \DateTime $updateTs
     *
     * @return CustomersBonus
     */
    public function setUpdateTs($updateTs)
    {
        $this->updateTs = $updateTs;

        return $this;
    }

    /**
     * Get updateTs
     *
     * @return \DateTime
     */
    public function getUpdateTs()
    {
        return $this->updateTs;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return CustomersBonus
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set residual
     *
     * @param integer $residual
     *
     * @return CustomersBonus
     */
    public function setResidual($residual)
    {
        $this->residual = $residual;

        return $this;
    }

    /**
     * Get residual
     *
     * @return integer
     */
    public function getResidual()
    {
        return $this->residual;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return CustomersBonus
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set operator
     *
     * @param string $operator
     *
     * @return CustomersBonus
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set validFrom
     *
     * @param \DateTime $validFrom
     *
     * @return CustomersBonus
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validFrom
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set durationDays
     *
     * @param integer $durationDays
     *
     * @return CustomersBonus
     */
    public function setDurationDays($durationDays)
    {
        $this->durationDays = $durationDays;

        return $this;
    }

    /**
     * Get durationDays
     *
     * @return integer
     */
    public function getDurationDays()
    {
        return $this->durationDays;
    }

    /**
     * Set validTo
     *
     * @param \DateTime $validTo
     *
     * @return CustomersBonus
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * Get validTo
     *
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return CustomersBonus
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set customer
     *
     * @param \SharengoCore\Entity\Customers $customer
     *
     * @return CustomersBonus
     */
    public function setCustomer(\SharengoCore\Entity\Customers $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \SharengoCore\Entity\Customers
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set webuser
     *
     * @param \SharengoCore\Entity\Webuser $webuser
     *
     * @return CustomersBonus
     */
    public function setWebuser(\SharengoCore\Entity\Webuser $webuser = null)
    {
        $this->webuser = $webuser;

        return $this;
    }

    /**
     * Get webuser
     *
     * @return \SharengoCore\Entity\Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * Set promocode
     *
     * @param \SharengoCore\Entity\Promocodes $promocode
     *
     * @return CustomersBonus
     */
    public function setPromocode(\SharengoCore\Entity\PromoCodes $promocode = null)
    {
        $this->promocode = $promocode;

        return $this;
    }

    /**
     * Get promocode
     *
     * @return \SharengoCore\Entity\Promocodes
     */
    public function getPromocode()
    {
        return $this->promocode;
    }

    /**
     * increments the residual by the given amount of minutes
     *
     * @var int $minutes
     * @return CustomersBonus
     * @throws NonPositiveIntegerException
     */
    public function incrementResidual($minutes)
    {
        if (!is_integer($minutes) || $minutes < 0) {
            throw new NonPositiveIntegerException();
        }

        $addedMinutes = $this->residual + $minutes;

        $this->residual = min($this->total, $addedMinutes);

        return $this;
    }

    public function impliesSubscriptionDiscount()
    {
        return null != $this->findDiscountedSubscriptionAmount();
    }

    public function findDiscountedSubscriptionAmount()
    {
        if (null != $this->getPromocode()) {
            $promoCodeInfo = $this->getPromocode()->getPromocodesinfo();
            $overriddenSubscriptionCost = $promoCodeInfo->getOverriddenSubscriptionCost();

            if (null !=  $overriddenSubscriptionCost &&
                is_numeric($overriddenSubscriptionCost)) {
                return $overriddenSubscriptionCost;
            }
        }

        return null;
    }
    
    public function canBeDeleted() {
        return $this->getTotal() == $this->getResidual() &&
               !$this->impliesSubscriptionDiscount();
    }
}

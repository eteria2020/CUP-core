<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="promo_codes_info")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\PromoCodesInfoRepository")
 */
class PromoCodesInfo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="promocodesinfo_id_seq", allocationSize=1, initialValue=1)
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=100, nullable=false)
     */
    private $type = 'promo';

    /**
     * @var integer
     *
     * @ORM\Column(name="minutes", type="integer", nullable=false)
     */
    private $minutes;

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
     * @var \DateTime
     *
     * @ORM\Column(name="bonus_valid_from", type="datetime", nullable=false)
     */
    private $bonusValidFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="bonus_valid_to", type="datetime", nullable=false)
     */
    private $bonusValidTo;

    /**
     * @var interger cost in eurocents
     *
     * @ORM\Column(name="overridden_subscription_cost", type="integer", nullable=true)
     */
    private $overriddenSubscriptionCost = null;
    
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
     * @return PromoCodesInfo
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
     * @return PromoCodesInfo
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
     * Set type
     *
     * @param string $type
     *
     * @return PromoCodesInfo
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
     * Set minutes
     *
     * @param integer $minutes
     *
     * @return PromoCodesInfo
     */
    public function setMinutes($minutes)
    {
        $this->minutes = $minutes;

        return $this;
    }

    /**
     * Get minutes
     *
     * @return integer
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * Set validFrom
     *
     * @param \DateTime $validFrom
     *
     * @return PromoCodesInfo
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
     * @return PromoCodesInfo
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
     * @return PromoCodesInfo
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
     * Get bonusValidFrom
     *
     * @return DateTime
     */
    public function getBonusValidFrom()
    {
        return $this->bonusValidFrom;
    }

    /**
     * Get bonusValidTo
     *
     * @return DateTime
     */
    public function getBonusValidTo()
    {
        return $this->bonusValidTo;
    }

    /**
     * Get overriddenSubscriptionCost
     *
     * @return integer
     */
    public function getOverriddenSubscriptionCost()
    {
        return $this->overriddenSubscriptionCost;
    }

    /**
     * Set overriddenSubscriptionCost
     *
     * @param integer $overriddenSubscriptionCost
     *
     * @return PromoCodesInfo
     */
    public function setOverriddenSubscriptionCost($overriddenSubscriptionCost)
    {
        $this->overriddenSubscriptionCost = $overriddenSubscriptionCost;

        return $this;
    }
    
    /**
     * Set webuser
     *
     * @param \SharengoCore\Entity\Webuser $webuser
     *
     * @return PromoCodesInfo
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
     * Check if this promo code updates subscription cost
     *
     * @return bool
     */
    public function changesSubscriptionCost()
    {
        return null != $this->overriddenSubscriptionCost &&
               $this->overriddenSubscriptionCost > 0;
    }
}

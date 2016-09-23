<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZoneBonus
 *
 * @ORM\Table(name="zone_bonus")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ZoneBonusRepository")
 */
class ZoneBonus
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zone_bonus_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="geo", type="text", nullable=false)
     */
    private $geo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var SharengoCore\Entity\Fleet[]
     *
     * @ORM\ManyToMany(targetEntity="Fleet", mappedBy="zoneBonus")
     */
    private $fleets;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="bonus_type", type="text", nullable=false)
     */
    private $bonusType;
    
    /**
     * @var string
     *
     * @ORM\Column(name="conditions", type="text", nullable=true)
     */
    private $conditions;
    
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \SharengoCore\Entity\Fleet[]
     */
    public function getFleets()
    {
        return $this->fleets;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fleets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set geo
     *
     * @param string $geo
     *
     * @return ZoneBonus
     */
    public function setGeo($geo)
    {
        $this->geo = $geo;

        return $this;
    }

    /**
     * Get geo
     *
     * @return string
     */
    public function getGeo()
    {
        return $this->geo;
    }
    
    /**
     * Set bonusType
     *
     * @param string $bonusType
     *
     * @return ZoneBonus
     */
    public function setBonusType($bonusType)
    {
        $this->bonusType = $bonusType;

        return $this;
    }

    /**
     * Get bonusType
     *
     * @return string
     */
    public function getBonusType()
    {
        return $this->bonusType;
    }
    
    /**
     * Set conditions
     *
     * @param string $conditions
     *
     * @return ZoneBonus
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Get conditions
     *
     * @return string
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return ZoneBonus
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
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add fleet
     *
     * @param \SharengoCore\Entity\Fleet $fleet
     *
     * @return ZoneBonus
     */
    public function addFleet(\SharengoCore\Entity\Fleet $fleet)
    {
        $this->fleets[] = $fleet;

        return $this;
    }

    /**
     * Remove fleet
     *
     * @param \SharengoCore\Entity\Fleet $fleet
     */
    public function removeFleet(\SharengoCore\Entity\Fleet $fleet)
    {
        $this->fleets->removeElement($fleet);
    }
}

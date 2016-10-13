<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZoneAlarms
 *
 * @ORM\Table(name="zone_alarms")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ZoneAlarmsRepository")
 */
class ZoneAlarms
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zone_alarms_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="geo", type="string", nullable=false)
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
     * @ORM\ManyToMany(targetEntity="Fleet", mappedBy="zoneAlarms")
     */
    private $fleets;

    /**
     * @var $string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;

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
     * @return ZoneAlarms
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
     * Set active
     *
     * @param boolean $active
     *
     * @return ZoneAlarms
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
     * @return ZoneAlarms
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

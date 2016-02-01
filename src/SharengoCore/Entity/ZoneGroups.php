<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Zone
 *
 * @ORM\Table(name="zone_groups")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ZoneGroupsRepository")
 */
class ZoneGroups
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zone_groups_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="company_id", type="integer", nullable=false)
     */
    private $companyId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="close_trip", type="boolean", nullable=false)
     */
    private $closeTrip;

    /**
     * @var string
     *
     * @ORM\Column(name="id_zone", type="string", nullable=false)
     */
    private $zonesList;

    private $zoneListText = "";

    /**
     * @var \Fleet
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     * })
     */
    private $fleet;



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
     * Set description
     *
     * @param string $description
     *
     * @return ZoneGroups
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
     * Set companyId
     *
     * @param integer $companyId
     *
     * @return ZoneGroups
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;

        return $this;
    }

    /**
     * Get companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

    /**
     * Set closeTrip
     *
     * @param boolean $closeTrip
     *
     * @return ZoneGroups
     */
    public function setCloseTrip($closeTrip)
    {
        $this->closeTrip = $closeTrip;

        return $this;
    }

    /**
     * Get closeTrip
     *
     * @return boolean
     */
    public function getCloseTrip()
    {
        return $this->closeTrip;
    }

    /**
     * Set zonesList
     *
     * @param string $zonesList
     *
     * @return ZoneGroups
     */
    public function setZonesList($zonesList)
    {
        $this->zonesList = $zonesList;

        return $this;
    }

    /**
     * Set zonesListText
     *
     * @param string $zonesListText
     *
     * @return ZoneGroups
     */
    public function setZonesListText($zonesListText)
    {
        $this->zonesListText = $zonesListText;

        return $this;
    }

    /**
     * Get zonesList
     *
     * @return string
     */
    public function getZonesList()
    {
        // convert something like {1,2,3} to array
        return explode(',', substr($this->zonesList, 1, strlen($this->zonesList) - 2));
    }

    /**
     * Get zonesListText
     *
     * @return string
     */
    public function getZonesListText()
    {
        return $this->zonesListText;
    }

    /**
     * Set fleet
     *
     * @param \SharengoCore\Entity\Fleet $fleet
     *
     * @return ZoneGroups
     */
    public function setFleet(\SharengoCore\Entity\Fleet $fleet = null)
    {
        $this->fleet = $fleet;

        return $this;
    }

    /**
     * Get fleet
     *
     * @return \SharengoCore\Entity\Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }
}

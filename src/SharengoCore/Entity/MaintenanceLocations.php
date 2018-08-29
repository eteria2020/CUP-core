<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaintenanceLocations
 *
 * @ORM\Table(name="maintenance_locations")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\MaintenanceLocationsRepository")
 */
class MaintenanceLocations
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="maintenance_locations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="text", nullable=false)
     */
    private $location;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var Fleets
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     * })
     */
    private $fleet;
 
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
    
    /**
     * Get fleet
     *
     * @return Fleet
     */
    public function getFleet() {
        return $this->fleet;
    }

}

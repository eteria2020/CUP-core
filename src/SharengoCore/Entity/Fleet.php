<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Countries
 *
 * @ORM\Table(name="fleets")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\FleetRepository")
 */
class Fleet
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="fleet_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
   
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", nullable=false)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", nullable=false)
     */
    private $latitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="zoom_level", type="integer", nullable=false)
     */
    private $zoomLevel;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     */
    private $isDefault = false;

    /**
     * @var string
     *
     * @ORM\Column(name="int_code", type="string", nullable=false)
     */
    private $intCode;

    /**
     * @var string
     *
     * @ORM\Column(name="invoice_header", type="text", nullable=false)
     */
    private $invoiceHeader;

    /**
     * @var ZoneAlarms[]
     *
     * @ORM\ManyToMany(targetEntity="ZoneAlarms", inversedBy="fleets")
     * @ORM\JoinTable(
     *  name="zone_alarms_fleets",
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="zone_alarm_id", referencedColumnName="id")
     *  },
     *  joinColumns={
     *      @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     *  }    
     * )
     */
    private $zoneAlarms;
    
    
    /**
     * @var ZoneAlarms[]
     *
     * @ORM\ManyToMany(targetEntity="ZoneBonus", inversedBy="fleets")
     * @ORM\JoinTable(
     *  name="zone_bonus_fleets",
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="zone_bonus_id", referencedColumnName="id")
     *  },
     *  joinColumns={
     *      @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     *  }    
     * )
     */
    private $zoneBonus;


    public function __construct($code, $name, $latitude, $longitude, $zoomLevel, $isDefault = false)
    {
        $this->code = $code;
        $this->name = $name;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->zoomLevel = $zoomLevel;
        $this->isDefault = $isDefault;
    }


    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        return $hydrator->extract($this);
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
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Get zoomLevel
     *
     * @return integer
     */
    public function getZoomLevel()
    {
        return $this->zoomLevel;
    }

    /**
     * Get isDefault
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return string
     */
    public function getIntCode()
    {
        return $this->intCode;
    }

    /**
     * @return string
     */
    public function getInvoiceHeader()
    {
        return $this->invoiceHeader;
    }

    /**
     * @return \SharengoCore\Entity\ZoneAlarms[]
     */
    public function getZoneAlarms()
    {
        return $this->zoneAlarms;
    }
}

<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Cars
 *
 * @ORM\Table(name="cars")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CarsRepository")
 */
class Cars {

    /**
     * @var string
     *
     * @ORM\Column(name="plate", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $plate;

    /**
     * @var string
     *
     * @ORM\Column(name="manufactures", type="text", nullable=true)
     */
    private $manufactures;

    /**
     * @var string
     *
     * @ORM\Column(name="model", type="text", nullable=true)
     */
    private $model;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="text", nullable=false)
     */
    private $label = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="int_cleanliness", type="string", nullable=false)
     */
    private $intCleanliness = 'clean';

    /**
     * @var string
     *
     * @ORM\Column(name="ext_cleanliness", type="string", nullable=false)
     */
    private $extCleanliness = 'clean';

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="damages", type="text", nullable=true)
     */
    private $damages;

    /**
     * @var integer
     *
     * @ORM\Column(name="battery", type="integer", nullable=false)
     */
    private $battery = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="frame", type="text", nullable=true)
     */
    private $frame;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", nullable=true)
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="firmware_version", type="text", nullable=true)
     */
    private $firmwareVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="software_version", type="text", nullable=true)
     */
    private $softwareVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="mac", type="text", nullable=true)
     */
    private $mac;

    /**
     * @var string
     *
     * @ORM\Column(name="imei", type="text", nullable=true)
     */
    private $imei;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_contact", type="datetimetz", nullable=true)
     */
    private $lastContact;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_location_time", type="datetimetz", nullable=true)
     */
    private $lastLocationTime;

    /**
     * @var boolean
     *
     * @ORM\Column(name="busy", type="boolean", nullable=true)
     */
    private $busy = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="hidden", type="boolean", nullable=true)
     */
    private $hidden = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="rpm", type="integer", nullable=false)
     */
    private $rpm = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="speed", type="integer", nullable=false)
     */
    private $speed = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="obc_in_use", type="integer", nullable=false)
     */
    private $obcInUse = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="obc_wl_size", type="integer", nullable=false)
     */
    private $obcWlSize = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="km", type="integer", nullable=false)
     */
    private $km = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="running", type="boolean", nullable=true)
     */
    private $running = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="parking", type="boolean", nullable=true)
     */
    private $parking = false;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    private $status = 'maintenance';

    /**
     * @var integer
     *
     * @ORM\Column(name="soc", type="integer", nullable=false)
     */
    private $soc = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="vin", type="text", nullable=true)
     */
    private $vin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="battery_safety", type="boolean", nullable=false)
     */
    private $batterySafety = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="battery_safety_ts", type="datetimetz", nullable=true)
     */
    private $batterySafetyTs;

    /**
     * @var string
     *
     * @ORM\Column(name="key_status", type="text", nullable=true)
     */
    private $keyStatus;

    /**
     * @var boolean
     *
     * @ORM\Column(name="charging", type="boolean", nullable=true)
     */
    private $charging = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="nogps", type="boolean", nullable=true)
     */
    private $nogps = false;

    /**
     * @ORM\OneToMany(targetEntity="Trips", mappedBy="car")
     */
    private $trips;

    /**
     * @var \Fleet
     *
     * @ORM\ManyToOne(targetEntity="Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $fleet;

    /**
     * @var \CarsInfo
     *
     * @ORM\OneToOne(targetEntity="CarsInfo", mappedBy="plate")
     */
    private $carsInfo;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @ORM\OneToMany(targetEntity="Reservations", mappedBy="car", cascade={"remove"})
     */
    private $reservations;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @ORM\OneToMany(targetEntity="ReservationsArchive", mappedBy="car", cascade={"remove"})
     */
    private $reservationsArchive;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @ORM\OneToMany(targetEntity="CarsMaintenance", mappedBy="carPlate", cascade={"remove"})
     */
    private $maintenances;

    public function __construct() {
        $this->trips = new ArrayCollection();
    }

    /**
     * Get plate
     *
     * @return string
     */
    public function getPlate() {
        return $this->plate;
    }

    /**
     * Set plate
     *
     * @param string $plate
     */
    public function setPlate($plate) {
        $this->plate = $plate;
    }

    /**
     * Set manufactures
     *
     * @param string $manufactures
     *
     * @return Cars
     */
    public function setManufactures($manufactures) {
        $this->manufactures = $manufactures;

        return $this;
    }

    /**
     * Get manufactures
     *
     * @return string
     */
    public function getManufactures() {
        return $this->manufactures;
    }

    /**
     * Set model
     *
     * @param string $model
     *
     * @return Cars
     */
    public function setModel($model) {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Cars
     */
    public function setLabel($label) {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Cars
     */
    public function setActive($active) {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Set intCleanliness
     *
     * @param string $intCleanliness
     *
     * @return Cars
     */
    public function setIntCleanliness($intCleanliness) {
        $this->intCleanliness = $intCleanliness;

        return $this;
    }

    /**
     * Get intCleanliness
     *
     * @return string
     */
    public function getIntCleanliness() {
        return $this->intCleanliness;
    }

    /**
     * Set extCleanliness
     *
     * @param string $extCleanliness
     *
     * @return Cars
     */
    public function setExtCleanliness($extCleanliness) {
        $this->extCleanliness = $extCleanliness;

        return $this;
    }

    /**
     * Get extCleanliness
     *
     * @return string
     */
    public function getExtCleanliness() {
        return $this->extCleanliness;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Cars
     */
    public function setNotes($notes) {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Cars
     */
    public function setLongitude($longitude) {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Cars
     */
    public function setLatitude($latitude) {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * Set damages
     *
     * @param array $damages
     *
     * @return Cars
     */
    public function setDamages(array $damages = null) {
        if (count($damages) > 0 &&
                null != $damages) {
            $this->damages = json_encode($damages);
        } else {
            $this->damages = null;
        }

        return $this;
    }

    /**
     * Get damages
     *
     * @return string
     */
    public function getDamages() {
        return $this->damages;
    }

    /**
     * Set battery
     *
     * @param integer $battery
     *
     * @return Cars
     */
    public function setBattery($battery) {
        $this->battery = $battery;

        return $this;
    }

    /**
     * Get battery
     *
     * @return integer
     */
    public function getBattery() {
        return $this->battery;
    }

    /**
     * Set frame
     *
     * @param string $frame
     *
     * @return Cars
     */
    public function setFrame($frame) {
        $this->frame = $frame;

        return $this;
    }

    /**
     * Get frame
     *
     * @return string
     */
    public function getFrame() {
        return $this->frame;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Cars
     */
    public function setLocation($location) {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation() {
        return $this->location;
    }

    /**
     * Set firmwareVersion
     *
     * @param string $firmwareVersion
     *
     * @return Cars
     */
    public function setFirmwareVersion($firmwareVersion) {
        $this->firmwareVersion = $firmwareVersion;

        return $this;
    }

    /**
     * Get firmwareVersion
     *
     * @return string
     */
    public function getFirmwareVersion() {
        return $this->firmwareVersion;
    }

    /**
     * Set softwareVersion
     *
     * @param string $softwareVersion
     *
     * @return Cars
     */
    public function setSoftwareVersion($softwareVersion) {
        $this->softwareVersion = $softwareVersion;

        return $this;
    }

    /**
     * Get softwareVersion
     *
     * @return string
     */
    public function getSoftwareVersion() {
        return $this->softwareVersion;
    }

    /**
     * Set mac
     *
     * @param string $mac
     *
     * @return Cars
     */
    public function setMac($mac) {
        $this->mac = $mac;

        return $this;
    }

    /**
     * Get mac
     *
     * @return string
     */
    public function getMac() {
        return $this->mac;
    }

    /**
     * Set imei
     *
     * @param string $imei
     *
     * @return Cars
     */
    public function setImei($imei) {
        $this->imei = $imei;

        return $this;
    }

    /**
     * Get imei
     *
     * @return string
     */
    public function getImei() {
        return $this->imei;
    }

    /**
     * Set lastContact
     *
     * @param \DateTime $lastContact
     *
     * @return Cars
     */
    public function setLastContact($lastContact) {
        $this->lastContact = $lastContact;

        return $this;
    }

    /**
     * Get lastContact
     *
     * @return \DateTime
     */
    public function getLastContact() {
        return $this->lastContact;
    }

    /**
     * Set lastLocationTime
     *
     * @param \DateTime $lastLocationTime
     *
     * @return Cars
     */
    public function setLastLocationTime($lastLocationTime) {
        $this->lastLocationTime = $lastLocationTime;

        return $this;
    }

    /**
     * Get lastLocationTime
     *
     * @return \DateTime
     */
    public function getLastLocationTime() {
        return $this->lastLocationTime;
    }

    /**
     * Set busy
     *
     * @param boolean $busy
     *
     * @return Cars
     */
    public function setBusy($busy) {
        $this->busy = $busy;

        return $this;
    }

    /**
     * Get busy
     *
     * @return boolean
     */
    public function getBusy() {
        return $this->busy;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Cars
     */
    public function setHidden($hidden) {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return boolean
     */
    public function getHidden() {
        return $this->hidden;
    }

    /**
     * Set rpm
     *
     * @param integer $rpm
     *
     * @return Cars
     */
    public function setRpm($rpm) {
        $this->rpm = $rpm;

        return $this;
    }

    /**
     * Get rpm
     *
     * @return integer
     */
    public function getRpm() {
        return $this->rpm;
    }

    /**
     * Set speed
     *
     * @param integer $speed
     *
     * @return Cars
     */
    public function setSpeed($speed) {
        $this->speed = $speed;

        return $this;
    }

    /**
     * Get speed
     *
     * @return integer
     */
    public function getSpeed() {
        return $this->speed;
    }

    /**
     * Set obcInUse
     *
     * @param integer $obcInUse
     *
     * @return Cars
     */
    public function setObcInUse($obcInUse) {
        $this->obcInUse = $obcInUse;

        return $this;
    }

    /**
     * Get obcInUse
     *
     * @return integer
     */
    public function getObcInUse() {
        return $this->obcInUse;
    }

    /**
     * Set obcWlSize
     *
     * @param integer $obcWlSize
     *
     * @return Cars
     */
    public function setObcWlSize($obcWlSize) {
        $this->obcWlSize = $obcWlSize;

        return $this;
    }

    /**
     * Get obcWlSize
     *
     * @return integer
     */
    public function getObcWlSize() {
        return $this->obcWlSize;
    }

    /**
     * Set km
     *
     * @param integer $km
     *
     * @return Cars
     */
    public function setKm($km) {
        $this->km = $km;

        return $this;
    }

    /**
     * Get km
     *
     * @return integer
     */
    public function getKm() {
        return $this->km;
    }

    /**
     * Set running
     *
     * @param boolean $running
     *
     * @return Cars
     */
    public function setRunning($running) {
        $this->running = $running;

        return $this;
    }

    /**
     * Get running
     *
     * @return boolean
     */
    public function getRunning() {
        return $this->running;
    }

    /**
     * Set parking
     *
     * @param boolean $parking
     *
     * @return Cars
     */
    public function setParking($parking) {
        $this->parking = $parking;

        return $this;
    }

    /**
     * Get parking
     *
     * @return boolean
     */
    public function getParking() {
        return $this->parking;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Cars
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set key soc
     *
     * @param int $soc
     *
     * @return Cars
     */
    public function setSoc($soc) {
        $this->soc = $soc;

        return $this;
    }

    /**
     * Get soc
     *
     * @return int
     */
    public function getSoc() {
        return $this->soc;
    }

    /**
     * Set key vin
     *
     * @param string $vin
     *
     * @return Cars
     */
    public function setVin($vin) {
        $this->vin = $vin;

        return $this;
    }

    /**
     * Get vin
     *
     * @return string
     */
    public function getVin() {
        return $this->vin;
    }

    /**
     * Set key keyStatus
     *
     * @param string $keyStatus
     *
     * @return Cars
     */
    public function setKeystatus($keyStatus) {
        $this->keyStatus = $keyStatus;

        return $this;
    }

    /**
     * Get keyStatus
     *
     * @return string
     */
    public function getKeystatus() {
        return $this->keyStatus;
    }

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator) {
        $extractedCar = $hydrator->extract($this);

        $extractedCar['fleet'] = $this->getFleet()->toArray($hydrator);

        return $extractedCar;
    }

    /**
     * @return boolean
     */
    public function getCharging() {
        return $this->charging;
    }

    /**
     * @param boolean
     * @return Cars
     */
    public function setCharging($charging) {
        $this->charging = $charging;
        return $this;
    }

    /**
     * Get fleet
     *
     * @return \SharengoCore\Entity\Fleet
     */
    public function getFleet() {
        return $this->fleet;
    }

    /**
     * Set fleet
     *
     * @param \SharengoCore\Entity\Fleet $fleet
     *
     * @return Cars
     */
    public function setFleet(\SharengoCore\Entity\Fleet $fleet) {
        $this->fleet = $fleet;

        return $this;
    }

    /**
     * Get  gps
     *
     * @return \SharengoCore\Entity\CarsInfo gps
     */
    public function getCarsInfoGps() {
        return $this->carsInfo->getGps();
    }

    /**
     * Get softwareVersion
     *
     * @return \SharengoCore\Entity\CarsInfo softwareVersion
     */
    public function getCarsInfoSoftwareVersion() {
        return $this->carsInfo->getSoftwareVersion();
    }

    /**
     * Get firmwareVersion
     *
     * @return \SharengoCore\Entity\CarsInfo firmwareVersion
     */
    public function getCarsInfoFirmwareVersion() {
        return $this->carsInfo->getFirmwareVersion();
    }

    /**
     * Get UnplugEnable
     * @return boolen
     */
    public function getCarsInfoUnplugEnable() {
        return $this->carsInfo->getUnplugEnable();
    }

    /**
     * Get batterySafety
     *
     * @return boolean
     */
    public function getBatterySafety() {
        return $this->batterySafety;
    }

    /**
     * Set battery safety
     *
     * @param boolean $batterySafety
     *
     * @return Cars
     */
    public function setBatterySafety($batterySafety) {
        $this->batterySafety = $batterySafety;

        return $this;
    }

    /**
     * Get batterySafetyTs
     *
     * @return \DateTime
     */
    public function getBatterySafetyTs() {
        return $this->batterySafetyTs;
    }

    /**
     * Get nogps
     *
     * @return boolean
     */
    public function getNogps() {
        return $this->nogps;
    }

}

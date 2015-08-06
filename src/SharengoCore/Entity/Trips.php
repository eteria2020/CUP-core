<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Trips
 *
 * @ORM\Table(name="trips", indexes={@ORM\Index(name="IDX_AA7370DAAE35528C", columns={"car_plate"}), @ORM\Index(name="IDX_AA7370DA9395C3F3", columns={"customer_id"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripsRepository")
 */
class Trips
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="trips_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp_beginning", type="datetimetz", nullable=false)
     */
    private $timestampBeginning;

    /**
     * @var integer
     *
     * @ORM\Column(name="km_beginning", type="integer", nullable=false)
     */
    private $kmBeginning;

    /**
     * @var integer
     *
     * @ORM\Column(name="battery_beginning", type="integer", nullable=false)
     */
    private $batteryBeginning;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude_beginning", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $longitudeBeginning;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude_beginning", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $latitudeBeginning;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_beginning", type="string", nullable=false)
     */
    private $geoBeginning;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="beginning_tx", type="datetimetz", nullable=false)
     */
    private $beginningTx;

    /**
     * @var string
     *
     * @ORM\Column(name="address_beginning", type="text", nullable=true)
     */
    private $addressBeginning;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp_end", type="datetimetz", nullable=false)
     */
    private $timestampEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="km_end", type="integer", nullable=false)
     */
    private $kmEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="battery_end", type="integer", nullable=false)
     */
    private $batteryEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude_end", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $longitudeEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude_end", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $latitudeEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="geo_end", type="string", nullable=false)
     */
    private $geoEnd;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_tx", type="datetimetz", nullable=false)
     */
    private $endTx;

    /**
     * @var string
     *
     * @ORM\Column(name="address_end", type="text", nullable=true)
     */
    private $addressEnd;

    /**
     * @var integer
     *
     * @ORM\Column(name="park_seconds", type="integer", nullable=false)
     */
    private $parkSeconds;

    /**
     * @var boolean
     *
     * @ORM\Column(name="payable", type="boolean", nullable=true)
     */
    private $payable = true;

    /**
     * @var \Cars
     *
     * @ORM\ManyToOne(targetEntity="Cars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="car_plate", referencedColumnName="plate")
     * })
     */
    private $car;

    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_accounted", type="boolean", nullable=false, options={"default" = FALSE})
     */
    private $isAccounted = false;



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
     * Set timestampBeginning
     *
     * @param \DateTime $timestampBeginning
     *
     * @return Trips
     */
    public function setTimestampBeginning($timestampBeginning)
    {
        $this->timestampBeginning = $timestampBeginning;

        return $this;
    }

    /**
     * Get timestampBeginning
     *
     * @return \DateTime
     */
    public function getTimestampBeginning()
    {
        return $this->timestampBeginning;
    }

    /**
     * Set kmBeginning
     *
     * @param integer $kmBeginning
     *
     * @return Trips
     */
    public function setKmBeginning($kmBeginning)
    {
        $this->kmBeginning = $kmBeginning;

        return $this;
    }

    /**
     * Get kmBeginning
     *
     * @return integer
     */
    public function getKmBeginning()
    {
        return $this->kmBeginning;
    }

    /**
     * Set batteryBeginning
     *
     * @param integer $batteryBeginning
     *
     * @return Trips
     */
    public function setBatteryBeginning($batteryBeginning)
    {
        $this->batteryBeginning = $batteryBeginning;

        return $this;
    }

    /**
     * Get batteryBeginning
     *
     * @return integer
     */
    public function getBatteryBeginning()
    {
        return $this->batteryBeginning;
    }

    /**
     * Set longitudeBeginning
     *
     * @param string $longitudeBeginning
     *
     * @return Trips
     */
    public function setLongitudeBeginning($longitudeBeginning)
    {
        $this->longitudeBeginning = $longitudeBeginning;

        return $this;
    }

    /**
     * Get longitudeBeginning
     *
     * @return string
     */
    public function getLongitudeBeginning()
    {
        return $this->longitudeBeginning;
    }

    /**
     * Set latitudeBeginning
     *
     * @param string $latitudeBeginning
     *
     * @return Trips
     */
    public function setLatitudeBeginning($latitudeBeginning)
    {
        $this->latitudeBeginning = $latitudeBeginning;

        return $this;
    }

    /**
     * Get latitudeBeginning
     *
     * @return string
     */
    public function getLatitudeBeginning()
    {
        return $this->latitudeBeginning;
    }

    /**
     * Set geoBeginning
     *
     * @param string $geoBeginning
     *
     * @return Trips
     */
    public function setGeoBeginning($geoBeginning)
    {
        $this->geoBeginning = $geoBeginning;

        return $this;
    }

    /**
     * Get geoBeginning
     *
     * @return string
     */
    public function getGeoBeginning()
    {
        return $this->geoBeginning;
    }

    /**
     * Set beginningTx
     *
     * @param \DateTime $beginningTx
     *
     * @return Trips
     */
    public function setBeginningTx($beginningTx)
    {
        $this->beginningTx = $beginningTx;

        return $this;
    }

    /**
     * Get beginningTx
     *
     * @return \DateTime
     */
    public function getBeginningTx()
    {
        return $this->beginningTx;
    }

    /**
     * Set addressBeginning
     *
     * @param string $addressBeginning
     *
     * @return Trips
     */
    public function setAddressBeginning($addressBeginning)
    {
        $this->addressBeginning = $addressBeginning;

        return $this;
    }

    /**
     * Get addressBeginning
     *
     * @return string
     */
    public function getAddressBeginning()
    {
        return $this->addressBeginning;
    }

    /**
     * Set timestampEnd
     *
     * @param \DateTime $timestampEnd
     *
     * @return Trips
     */
    public function setTimestampEnd($timestampEnd)
    {
        $this->timestampEnd = $timestampEnd;

        return $this;
    }

    /**
     * Get timestampEnd
     *
     * @return \DateTime
     */
    public function getTimestampEnd()
    {
        return $this->timestampEnd;
    }

    /**
     * Set kmEnd
     *
     * @param integer $kmEnd
     *
     * @return Trips
     */
    public function setKmEnd($kmEnd)
    {
        $this->kmEnd = $kmEnd;

        return $this;
    }

    /**
     * Get kmEnd
     *
     * @return integer
     */
    public function getKmEnd()
    {
        return $this->kmEnd;
    }

    /**
     * Set batteryEnd
     *
     * @param integer $batteryEnd
     *
     * @return Trips
     */
    public function setBatteryEnd($batteryEnd)
    {
        $this->batteryEnd = $batteryEnd;

        return $this;
    }

    /**
     * Get batteryEnd
     *
     * @return integer
     */
    public function getBatteryEnd()
    {
        return $this->batteryEnd;
    }

    /**
     * Set longitudeEnd
     *
     * @param string $longitudeEnd
     *
     * @return Trips
     */
    public function setLongitudeEnd($longitudeEnd)
    {
        $this->longitudeEnd = $longitudeEnd;

        return $this;
    }

    /**
     * Get longitudeEnd
     *
     * @return string
     */
    public function getLongitudeEnd()
    {
        return $this->longitudeEnd;
    }

    /**
     * Set latitudeEnd
     *
     * @param string $latitudeEnd
     *
     * @return Trips
     */
    public function setLatitudeEnd($latitudeEnd)
    {
        $this->latitudeEnd = $latitudeEnd;

        return $this;
    }

    /**
     * Get latitudeEnd
     *
     * @return string
     */
    public function getLatitudeEnd()
    {
        return $this->latitudeEnd;
    }

    /**
     * Set geoEnd
     *
     * @param string $geoEnd
     *
     * @return Trips
     */
    public function setGeoEnd($geoEnd)
    {
        $this->geoEnd = $geoEnd;

        return $this;
    }

    /**
     * Get geoEnd
     *
     * @return string
     */
    public function getGeoEnd()
    {
        return $this->geoEnd;
    }

    /**
     * Set endTx
     *
     * @param \DateTime $endTx
     *
     * @return Trips
     */
    public function setEndTx($endTx)
    {
        $this->endTx = $endTx;

        return $this;
    }

    /**
     * Get endTx
     *
     * @return \DateTime
     */
    public function getEndTx()
    {
        return $this->endTx;
    }

    /**
     * Set addressEnd
     *
     * @param string $addressEnd
     *
     * @return Trips
     */
    public function setAddressEnd($addressEnd)
    {
        $this->addressEnd = $addressEnd;

        return $this;
    }

    /**
     * Get addressEnd
     *
     * @return string
     */
    public function getAddressEnd()
    {
        return $this->addressEnd;
    }

    /**
     * Set parkSeconds
     *
     * @param integer $parkSeconds
     *
     * @return Trips
     */
    public function setParkSeconds($parkSeconds)
    {
        $this->parkSeconds = $parkSeconds;

        return $this;
    }

    /**
     * Get parkSeconds
     *
     * @return integer
     */
    public function getParkSeconds()
    {
        return $this->parkSeconds;
    }

    /**
     * Set payable
     *
     * @param boolean $payable
     *
     * @return Trips
     */
    public function setPayable($payable)
    {
        $this->payable = $payable;

        return $this;
    }

    /**
     * Get payable
     *
     * @return boolean
     */
    public function getPayable()
    {
        return $this->payable;
    }

    /**
     * Set car
     *
     * @param \SharengoCore\Entity\Cars $car
     *
     * @return Trips
     */
    public function setCar(\SharengoCore\Entity\Cars $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get getCar
     *
     * @return \SharengoCore\Entity\Cars
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * Set customer
     *
     * @param \SharengoCore\Entity\Customers $customer
     *
     * @return Trips
     */
    public function setCustomer(\SharengoCore\Entity\Customers $customer = null)
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
     * Set isAccounted
     *
     * @param boolean $isAccounted
     *
     * @return Trips
     */
    public function setIsAccounted($isAccounted)
    {
        $this->isAccounted = $isAccounted;

        return $this;
    }

    /**
     * Get isAccounted
     *
     * @return boolean
     */
    public function getIsAccounted()
    {
        return $this->isAccounted;
    }

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $customer = $this->getCustomer();
        if ($customer !== null) {
            $customer = $customer->toArray($hydrator);
        }

        $car = $this->getCar();
        if ($car !== null) {
            $car = $car->toArray($hydrator);
        }

        $extractedTrip = $hydrator->extract($this);
        $extractedTrip['customer'] = $customer;
        $extractedTrip['car'] = $car;
        
        return $extractedTrip;
    }

    /**
     * checks if a trip should be accounted
     * For the moment this checks only if the user is in gold list
     *
     * @return boolean
     */
    public function isAccountable()
    {
        return !$this->getCustomer()->getGoldList();
    }
}

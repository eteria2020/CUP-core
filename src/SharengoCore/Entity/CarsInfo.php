<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarsInfo
 *
 * @ORM\Table(name="cars_info")
 * @ORM\Entity
 */
class CarsInfo {

    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="Cars", inversedBy="carsInfo")
     * @ORM\JoinColumn(name="car_plate", referencedColumnName="plate")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $plate;

    /**
     * @var string
     *
     * @ORM\Column(name="int_lon", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $intLongitude;

    /**
     * @var string
     *
     * @ORM\Column(name="int_lat", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $intLatitude;

    /**
     * @var string
     *
     * @ORM\Column(name="int_geo", type="string", nullable=true)
     */
    private $intGeo;

    /**
     * @var string
     *
     * @ORM\Column(name="gprs_lon", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $extLongitude;

    /**
     * @var string
     *
     * @ORM\Column(name="gprs_lat", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $extLatitude;

    /**
     * @var string
     *
     * @ORM\Column(name="gprs_geo", type="string", nullable=true)
     */
    private $extGeo;

    /**
     * @var string
     *
     * @ORM\Column(name="fw_ver", type="text", nullable=true)
     */
    private $firmwareVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="hw_ver", type="text", nullable=true)
     */
    private $hardwareVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="sw_ver", type="text", nullable=true)
     */
    private $softwareVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="sdk", type="text", nullable=true)
     */
    private $sdk;

    /**
     * @var string
     *
     * @ORM\Column(name="sdk_ver", type="text", nullable=true)
     */
    private $sdkVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="gsm_ver", type="text", nullable=true)
     */
    private $gsmVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="android_device", type="text", nullable=true)
     */
    private $androidDevice;

    /**
     * @var string
     *
     * @ORM\Column(name="android_build", type="text", nullable=true)
     */
    private $androidBuild;

    /**
     * @var string
     *
     * @ORM\Column(name="tbox_sw", type="text", nullable=true)
     */
    private $tboxSoftware;

    /**
     * @var string
     *
     * @ORM\Column(name="tbox_hw", type="text", nullable=true)
     */
    private $tboxHardware;

    /**
     * @var string
     *
     * @ORM\Column(name="mcu_model", type="text", nullable=true)
     */
    private $mcuModel;

    /**
     * @var string
     *
     * @ORM\Column(name="mcu", type="text", nullable=true)
     */
    private $mcu;

    /**
     * @var string
     *
     * @ORM\Column(name="hw_version", type="text", nullable=true)
     */
    private $hwVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="hb_ver", type="text", nullable=true)
     */
    private $hbVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="vehicle_type", type="text", nullable=true)
     */
    private $vehicleType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastupdate", type="datetime", nullable=true)
     */
    private $lastUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="gps", type="text", nullable=true)
     */
    private $gps;

    /**
     * @var string
     *
     * @ORM\Column(name="insurance_company", type="text", nullable=true)
     */
    private $insuranceCompany;

    /**
     * @var string
     *
     * @ORM\Column(name="insurance_number", type="text", nullable=true)
     */
    private $insuranceNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insurance_expiry", type="datetime", nullable=true)
     */
    private $insuranceExpiry;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insurance_valid_from", type="datetime", nullable=true)
     */
    private $isuranceValidFrom;


    public function __construct() {

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
     * Get firmwareVersion
     *
     * @return string
     */
    public function getFirmwareVersion() {
        return $this->firmwareVersion;
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
     * Get gps
     *
     * @return string
     */
    public function getGps() {
        return $this->gps;
    }

    /**
     * Get insurance company
     *
     * @return string
     */
    public function getInsuranceCompany() {
        return $this->insuranceCompany;
    }

    /**
     * Set insurance company
     * 
     * @param $company
     * @return string
     */
    public function setInsuranceCompany($company) {
        $this->insuranceCompany = $company;
        return $this->insuranceCompany;
    }
    
    /**
     * Get insurance number
     *
     * @return string
     */
    public function getInsuranceNumber() {
        return $this->insuranceNumber;
    }

    /**
     * Set insurance number
     *
     * @param $number
     * @return string
     */
    public function setInsuranceNumber($number) {
        $this->insuranceNumber = $number;
        return $this->insuranceNumber;
    }
    
    /**
     * Get insurance expiry
     *
     * @return \DateTime
     */
    public function getInsuranceExpiry() {
        return $this->insuranceExpiry;
    }

    /**
     * Set insurance expiry
     *
     * @param $expiry
     * @return \DateTime
     */
    public function setInsuranceExpiry($expiry) {
        $this->insuranceExpiry = $expiry;
        return $this->insuranceExpiry;
    }
    
    /**
    * Get insurance velid from
    *
    * @return \DateTime
    */
    public function getInsuranceValidFrom() {
        return $this->isuranceValidFrom;
    }

    /**
     * Set insurance expiry
     *
     * @param $expiry
     * @return \DateTime
     */
    public function setInsuranceValidFrom($validFrom) {
        $this->isuranceValidFrom = $validFrom;
        return $this->isuranceValidFrom;
    }
}

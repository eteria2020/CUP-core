<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForeignDriversLicesUpload
 *
 * @ORM\Table(name="foreign_drivers_license_upload")
 * @ORM\Entity
 */
class ForeignDriversLicenseUpload
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="foreign_drivers_license_upload_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers", inversedBy="foreignDriversLicenseUploads")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_name", type="string", nullable=true)
     */
    private $customerName;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_surname", type="string", nullable=true)
     */
    private $customerSurname;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_birth_town", type="string", nullable=true)
     */
    private $customerBirthTown;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_birth_province", type="string", nullable=true)
     */
    private $customerBirthProvince;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_birth_date", type="date", nullable=true)
     */
    private $customerBirthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_country", type="string", length=2, nullable=true)
     */
    private $customerCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_town", type="string", nullable=true)
     */
    private $customerTown;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_address", type="string", nullable=true)
     */
    private $customerAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="drivers_license_number", type="string", nullable=true)
     */
    private $driversLicenseNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_authority", type="string", nullable=true)
     */
    private $driversLicenseAuthority;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_country", type="string", length=2, nullable=true)
     */
    private $driverLicenseCountry;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="driver_license_release_date", type="date", nullable=true)
     */
    private $driverLicenseReleaseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_firstname", type="string", length=255, nullable=true)
     */
    private $driverLicenseName;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_surname", type="string", length=255, nullable=true)
     */
    private $driverLicenseSurname;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_categories", type="string", nullable=true)
     */
    private $driversLicenseCategories;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="driver_license_expire", type="date", nullable=true)
     */
    private $driversLicenseExpire;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", nullable=false)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="file_type", type="string", nullable=true)
     */
    private $fileType;

    /**
     * @var string
     *
     * @ORM\Column(name="file_location", type="string", nullable=true)
     */
    private $fileLocation;

    /**
     * @var int
     *
     * @ORM\Column(name="file_size", type="integer", nullable=true)
     */
    private $fileSize;

    public function __construct(
        Customers $customer,
        $fileName,
        $fileType = null,
        $fileLocation = null,
        $fileSize = null
    ) {
        $this->customer = $customer;
        $this->customerName = $customer->getName();
        $this->customerSurname = $customer->getSurname();
        $this->customerBirthTown = $customer->getBirthTown();
        $this->customerBirthProvince = $customer->getBirthProvince();
        $this->customerBirthDate = $customer->getBirthDate();
        $this->customerCountry = $customer->getCountry();
        $this->customerTown = $customer->getTown();
        $this->customerAddress = $customer->getAddress();
        $this->driversLicenseNumber = $customer->getDriverLicense();
        $this->driversLicenseAuthority = $customer->getDriverLicenseAuthority();
        $this->driverLicenseCountry = $customer->getDriverLicenseCountry();
        $this->driverLicenseReleaseDate = $customer->getDriverLicenseReleaseDate();
        $this->driverLicenseName = $customer->getDriverLicenseName();
        $this->driverLicenseSurname = $customer->getDriverLicenseSurname();
        $this->driversLicenseCategories = $customer->getDriverLicenseCategories();
        $this->driversLicenseExpire = $customer->getDriverLicenseExpire();
        $this->fileName = $fileName;
        $this->fileType = $fileType;
        $this->fileLocation = $fileLocation;
        $this->fileSize = $fileSize;
    }
}

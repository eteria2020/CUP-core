<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForeignDriversLicesUpload
 *
 * @ORM\Table(name="foreign_drivers_license_upload")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ForeignDriversLicenseUploadRepository")
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
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
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
     * @ORM\Column(name="customer_birth_country", type="string", nullable=true)
     */
    private $customerBirthCountry;

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
     * @ORM\Column(name="drivers_license_authority", type="string", nullable=true)
     */
    private $driversLicenseAuthority;

    /**
     * @var string
     *
     * @ORM\Column(name="drivers_license_country", type="string", length=2, nullable=true)
     */
    private $driversLicenseCountry;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="drivers_license_release_date", type="date", nullable=true)
     */
    private $driversLicenseReleaseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="drivers_license_firstname", type="string", length=255, nullable=true)
     */
    private $driversLicenseName;

    /**
     * @var string
     *
     * @ORM\Column(name="drivers_license_surname", type="string", length=255, nullable=true)
     */
    private $driversLicenseSurname;

    /**
     * @var string
     *
     * @ORM\Column(name="drivers_license_categories", type="string", nullable=true)
     */
    private $driversLicenseCategories;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="drivers_license_expire", type="date", nullable=true)
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

    /**
     * @var bool
     *
     * @ORM\Column(name="valid", type="boolean", nullable=true)
     */
    private $valid;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="validated_at", type="datetime", nullable=true)
     */
    private $validatedAt;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="validated_by", referencedColumnName="id", nullable=true)
     * })
     */
    private $validatedBy;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="uploaded_at", type="datetime", nullable=true)
     */
    private $uploadedAt;

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
        $this->customerBirthCoutry = $customer->getBirthCountry();
        $this->customerBirthDate = $customer->getBirthDate();
        $this->customerCountry = $customer->getCountry();
        $this->customerTown = $customer->getTown();
        $this->customerAddress = $customer->getAddress();
        $this->driversLicenseNumber = $customer->getDriverLicense();
        $this->driversLicenseAuthority = $customer->getDriverLicenseAuthority();
        $this->driversLicenseCountry = $customer->getDriverLicenseCountry();
        $this->driversLicenseReleaseDate = $customer->getDriverLicenseReleaseDate();
        $this->driversLicenseName = $customer->getDriverLicenseName();
        $this->driversLicenseSurname = $customer->getDriverLicenseSurname();
        $this->driversLicenseCategories = $customer->getDriverLicenseCategories();
        $this->driversLicenseExpire = $customer->getDriverLicenseExpire();
        $this->fileName = $fileName;
        $this->fileType = $fileType;
        $this->fileLocation = $fileLocation;
        $this->fileSize = $fileSize;
        $this->uploadedAt = date_create();
    }

    public function id()
    {
        return $this->id;
    }

    /**
     * @return Customers
     */
    public function customer()
    {
        return $this->customer;
    }

    public function customerId()
    {
        return $this->customer->getId();
    }

    public function customerName()
    {
        return $this->customerName;
    }

    public function customerSurname()
    {
        return $this->customerSurname;
    }

    public function customerAddress()
    {
        return $this->customerAddress . ',' . $this->customerTown . ', ' .
            $this->customerCountry;
    }

    public function customerBirthDate()
    {
        return $this->customerBirthDate;
    }

    public function customerBirthPlace()
    {
        return $this->customerBirthTown . ' (' . $this->customerBirthProvince . '), ' .
            $this->customerBirthCountry;
    }

    public function driversLicenseNumber()
    {
        return $this->driversLicenseNumber;
    }

    public function driversLicenseAuthority()
    {
        return $this->driversLicenseAuthority;
    }

    public function driversLicenseCountry()
    {
        return $this->driversLicenseCountry;
    }

    public function driversLicenseReleaseDate()
    {
        return $this->driversLicenseReleaseDate;
    }

    public function driversLicenseName()
    {
        return $this->driversLicenseName . ' ' . $this->driversLicenseSurname;
    }

    public function driversLicenseCategories()
    {
        return $this->driversLicenseCategories;
    }

    public function driversLicenseExpire()
    {
        return $this->driversLicenseExpire;
    }

    public function fileLocation()
    {
        return $this->fileLocation;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->valid;
    }

    /**
     * @var Webuser $webuser
     * @return static
     */
    public function validate(Webuser $webuser)
    {
        $this->valid = true;
        $this->validatedAt = date_create();
        $this->validatedBy = $webuser;

        return $this;
    }
}

<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Customers
 *
 * @ORM\Table(name="customers")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CustomersRepository")
 */
class Customers
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customers_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="text", nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="text", nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="text", nullable=true)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birth_date", type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="birth_town", type="text", nullable=true)
     */
    private $birthTown;

    /**
     * @var string
     *
     * @ORM\Column(name="birth_province", type="text", nullable=true)
     */
    private $birthProvince;

    /**
     * @var string
     *
     * @ORM\Column(name="birth_country", type="string", length=2, nullable=true)
     */
    private $birthCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="vat", type="text", nullable=true)
     */
    private $vat;

    /**
     * @var string
     *
     * @ORM\Column(name="tax_code", type="text", nullable=true)
     */
    private $taxCode;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=2, nullable=true)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="province", type="text", nullable=true)
     */
    private $province;

    /**
     * @var string
     *
     * @ORM\Column(name="town", type="text", nullable=true)
     */
    private $town;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="address_info", type="text", nullable=true)
     */
    private $addressInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="text", nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="text", nullable=true)
     */
    private $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="text", nullable=true)
     */
    private $fax;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license", type="text", nullable=true)
     */
    private $driverLicense;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_categories", type="string", nullable=true)
     */
    private $driverLicenseCategories;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_authority", type="string", nullable=true)
     */
    private $driverLicenseAuthority;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_country", type="string", length=2, nullable=true)
     */
    private $driverLicenseCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_release_date", type="date", nullable=true)
     */
    private $driverLicenseReleaseDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="driver_license_expire", type="date", nullable=true)
     */
    private $driverLicenseExpire;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_license_name", type="string", nullable=true)
     */
    private $driverLicenseName;

    /**
     * @var string
     *
     * @ORM\Column(name="pin", type="string", length=4, nullable=true)
     */
    private $pin;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="card_code", type="text", nullable=true)
     */
    private $cardCode;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetimetz", nullable=true)
     */
    private $insertedTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="update_id", type="bigint", nullable=true)
     */
    private $updateId;

    /**
     * @var integer
     *
     * @ORM\Column(name="update_ts", type="bigint", nullable=true)
     */
    private $updateTs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="registration_completed", type="boolean", options={"default"=false})
     */
    private $registrationCompleted = false;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="text", nullable=true)
     */
    private $hash;

    /**
     * @var boolean
     *
     * @ORM\Column(name="first_payment_completed", type="boolean", options={"default"=false})
     */
    private $firstPaymentCompleted = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="discount_rate", type="integer", nullable=true)
     */
    private $discountRate;

    public function toArray()
    {
        return get_object_vars($this);
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
     * Set email
     *
     * @param string $email
     *
     * @return Customers
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Customers
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Customers
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set surname
     *
     * @param string $surname
     *
     * @return Customers
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set gender
     *
     * @param string $gender
     *
     * @return Customers
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     *
     * @return Customers
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * Set birthTown
     *
     * @param string $birthTown
     *
     * @return Customers
     */
    public function setBirthTown($birthTown)
    {
        $this->birthTown = $birthTown;

        return $this;
    }

    /**
     * Get birthTown
     *
     * @return string
     */
    public function getBirthTown()
    {
        return $this->birthTown;
    }

    /**
     * Set birthProvince
     *
     * @param string $birthProvince
     *
     * @return Customers
     */
    public function setBirthProvince($birthProvince)
    {
        $this->birthProvince = $birthProvince;

        return $this;
    }

    /**
     * Get birthProvince
     *
     * @return string
     */
    public function getBirthProvince()
    {
        return $this->birthProvince;
    }

    /**
     * Set birthCountry
     *
     * @param string $birthCountry
     *
     * @return Customers
     */
    public function setBirthCountry($birthCountry)
    {
        $this->birthCountry = $birthCountry;

        return $this;
    }

    /**
     * Get birthCountry
     *
     * @return string
     */
    public function getBirthCountry()
    {
        return $this->birthCountry;
    }

    /**
     * Set vat
     *
     * @param string $vat
     *
     * @return Customers
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Set taxCode
     *
     * @param string $taxCode
     *
     * @return Customers
     */
    public function setTaxCode($taxCode)
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    /**
     * Get taxCode
     *
     * @return string
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * Set language
     *
     * @param string $language
     *
     * @return Customers
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Customers
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set province
     *
     * @param string $province
     *
     * @return Customers
     */
    public function setProvince($province)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return string
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set town
     *
     * @param string $town
     *
     * @return Customers
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Customers
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set addressInfo
     *
     * @param string $addressInfo
     *
     * @return Customers
     */
    public function setAddressInfo($addressInfo)
    {
        $this->addressInfo = $addressInfo;

        return $this;
    }

    /**
     * Get addressInfo
     *
     * @return string
     */
    public function getAddressInfo()
    {
        return $this->addressInfo;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     *
     * @return Customers
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Customers
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Customers
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set fax
     *
     * @param string $fax
     *
     * @return Customers
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set driverLicense
     *
     * @param string $driverLicense
     *
     * @return Customers
     */
    public function setDriverLicense($driverLicense)
    {
        $this->driverLicense = $driverLicense;

        return $this;
    }

    /**
     * Get driverLicense
     *
     * @return string
     */
    public function getDriverLicense()
    {
        return $this->driverLicense;
    }

    /**
     * Set driverLicenseCategories
     *
     * @param string $driverLicenseCategories
     *
     * @return Customers
     */
    public function setDriverLicenseCategories($driverLicenseCategories)
    {
        $this->driverLicenseCategories = $driverLicenseCategories;

        return $this;
    }

    /**
     * Get driverLicenseCategories
     *
     * @return string
     */
    public function getDriverLicenseCategories()
    {
        return $this->driverLicenseCategories;
    }

    /**
     * Set driverLicenseExpire
     *
     * @param \DateTime $driverLicenseExpire
     *
     * @return Customers
     */
    public function setDriverLicenseExpire($driverLicenseExpire)
    {
        $this->driverLicenseExpire = $driverLicenseExpire;

        return $this;
    }

    /**
     * Get driverLicenseExpire
     *
     * @return \DateTime
     */
    public function getDriverLicenseExpire()
    {
        return $this->driverLicenseExpire;
    }

    /**
     * Set pin
     *
     * @param string $pin
     *
     * @return Customers
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Customers
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set cardCode
     *
     * @param string $cardCode
     *
     * @return Customers
     */
    public function setCardCode($cardCode)
    {
        $this->cardCode = $cardCode;

        return $this;
    }

    /**
     * Get cardCode
     *
     * @return string
     */
    public function getCardCode()
    {
        return $this->cardCode;
    }

    /**
     * Set insertedTs
     *
     * @param \DateTime $insertedTs
     *
     * @return Customers
     */
    public function setInsertedTs($insertedTs)
    {
        $this->insertedTs = $insertedTs;

        return $this;
    }

    /**
     * Get insertedTs
     *
     * @return \DateTime
     */
    public function getInsertedTs()
    {
        return $this->insertedTs;
    }

    /**
     * Set updateId
     *
     * @param integer $updateId
     *
     * @return Customers
     */
    public function setUpdateId($updateId)
    {
        $this->updateId = $updateId;

        return $this;
    }

    /**
     * Get updateId
     *
     * @return integer
     */
    public function getUpdateId()
    {
        return $this->updateId;
    }

    /**
     * Set updateTs
     *
     * @param integer $updateTs
     *
     * @return Customers
     */
    public function setUpdateTs($updateTs)
    {
        $this->updateTs = $updateTs;

        return $this;
    }

    /**
     * Get updateTs
     *
     * @return integer
     */
    public function getUpdateTs()
    {
        return $this->updateTs;
    }

    /**
     * Set driverLicenseAuthority
     *
     * @param string $driverLicenseAuthority
     *
     * @return Customers
     */
    public function setDriverLicenseAuthority($driverLicenseAuthority)
    {
        $this->driverLicenseAuthority = $driverLicenseAuthority;

        return $this;
    }

    /**
     * Get driverLicenseAuthority
     *
     * @return string
     */
    public function getDriverLicenseAuthority()
    {
        return $this->driverLicenseAuthority;
    }

    /**
     * Set driverLicenseCountry
     *
     * @param string $driverLicenseCountry
     *
     * @return Customers
     */
    public function setDriverLicenseCountry($driverLicenseCountry)
    {
        $this->driverLicenseCountry = $driverLicenseCountry;

        return $this;
    }

    /**
     * Get driverLicenseCountry
     *
     * @return string
     */
    public function getDriverLicenseCountry()
    {
        return $this->driverLicenseCountry;
    }

    /**
     * Set driverLicenseReleaseDate
     *
     * @param \DateTime $driverLicenseReleaseDate
     *
     * @return Customers
     */
    public function setDriverLicenseReleaseDate($driverLicenseReleaseDate)
    {
        $this->driverLicenseReleaseDate = $driverLicenseReleaseDate;

        return $this;
    }

    /**
     * Get driverLicenseReleaseDate
     *
     * @return \DateTime
     */
    public function getDriverLicenseReleaseDate()
    {
        return $this->driverLicenseReleaseDate;
    }

    /**
     * Set driverLicenseName
     *
     * @param string $driverLicenseName
     *
     * @return Customers
     */
    public function setDriverLicenseName($driverLicenseName)
    {
        $this->driverLicenseName = $driverLicenseName;

        return $this;
    }

    /**
     * Get driverLicenseName
     *
     * @return string
     */
    public function getDriverLicenseName()
    {
        return $this->driverLicenseName;
    }

    /**
     * Set registrationCompleted
     *
     * @param boolean $registrationCompleted
     *
     * @return Customers
     */
    public function setRegistrationCompleted($registrationCompleted)
    {
        $this->registrationCompleted = $registrationCompleted;

        return $this;
    }

    /**
     * Get registrationCompleted
     *
     * @return boolean
     */
    public function getRegistrationCompleted()
    {
        return $this->registrationCompleted;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return Customers
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set firstPaymentCompleted
     *
     * @param boolean $firstPaymentCompleted
     *
     * @return Customers
     */
    public function setFirstPaymentCompleted($firstPaymentCompleted)
    {
        $this->firstPaymentCompleted = $firstPaymentCompleted;

        return $this;
    }

    /**
     * Get firstPaymentCompleted
     *
     * @return boolean
     */
    public function getFirstPaymentCompleted()
    {
        return $this->firstPaymentCompleted;
    }

    /**
     * Set discountRate
     *
     * @param float $discountRate
     *
     * @return Customers
     */
    public function setDiscountRate($discountRate)
    {
        $this->discountRate = (int) $discountRate;

        return $this;
    }

    /**
     * Get discountRate
     *
     * @return boolean
     */
    public function getDiscountRate()
    {
        return $this->discountRate;
    }
}

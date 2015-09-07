<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * Customers
 *
 * @ORM\Table(name="customers", uniqueConstraints={@ORM\UniqueConstraint(name="email_uk", columns={"email"}), @ORM\UniqueConstraint(name="tax_code_uk", columns={"tax_code"})})
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
     * @var \DateTime
     *
     * @ORM\Column(name="driver_license_expire", type="date", nullable=true)
     */
    private $driverLicenseExpire;

    /**
     * @var string
     *
     * @ORM\Column(name="pin", type="text", nullable=false)
     */
    private $pin;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=true)
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
     * @var string
     *
     * @ORM\Column(name="driver_license_authority", type="string", length=255, nullable=true)
     */
    private $driverLicenseAuthority;

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
     * @ORM\Column(name="driver_license_name", type="string", length=255, nullable=true)
     */
    private $driverLicenseName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="registration_completed", type="boolean", nullable=false)
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
     * @ORM\Column(name="first_payment_completed", type="boolean", nullable=false)
     */
    private $firstPaymentCompleted = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="discount_rate", type="integer", nullable=true)
     */
    private $discountRate;

    /**
     * @var integer
     *
     * @ORM\Column(name="reprofiling_option", type="integer", nullable=false)
     */
    private $reprofilingOption = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="profiling_counter", type="integer", nullable=false)
     */
    private $profilingCounter = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="gold_list", type="boolean", nullable=false)
     */
    private $goldList = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="maintainer", type="boolean", nullable=false)
     */
    private $maintainer = false;

    /**
     * @var \Cards
     *
     * @ORM\OneToOne(targetEntity="Cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_code", referencedColumnName="code")
     * })
     */
    private $card;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @ORM\OneToMany(targetEntity="CustomersBonus", mappedBy="customer")
     */
    private $customersbonuses;

    /**
     * @var boolean
     *
     * @ORM\Column(name="general_condition1", type="boolean", nullable=false)
     */
    private $generalCondition1 = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="general_condition2", type="boolean", nullable=false)
     */
    private $generalCondition2 = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="regulation_condition1", type="boolean", nullable=false)
     */
    private $regulationCondition1 = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="regulation_condition2", type="boolean", nullable=false)
     */
    private $regulationCondition2 = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="privacy_condition", type="boolean", nullable=false)
     */
    private $privacyCondition = false;

    /**
     * @var boolean false if a payment failed for the customer
     * if false we don't try other payments
     * returns true when the payment has correct outcome from the admin area
     *
     * @ORM\Column(name="payment_able", type="boolean", options={"default" = TRUE})
     */
    private $paymentAble = true;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @ORM\OneToMany(targetEntity="Trips", mappedBy="customer")
     */
    private $trips;


    public function __construct()
    {
        $this->insertedTs = date('Y-m-d h:i:s');
    }

    /**
     * @param DoctrineHydrator
     * @return mixed[]
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $card = $this->getCard();
        if ($card !== null) {
            $card = $card->toArray($hydrator);
        }
        $extractedCustomer = $hydrator->extract($this);
        $extractedCustomer['card'] = $card;

        return $extractedCustomer;
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
     * Get primary pin
     *
     * @return string
     */
    public function getPrimaryPin()
    {
        $pins = json_decode($this->pin, true);
        return $pins['primary'];
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
     * @param integer $discountRate
     *
     * @return Customers
     */
    public function setDiscountRate($discountRate)
    {
        $this->discountRate = $discountRate;

        return $this;
    }

    /**
     * Get discountRate
     *
     * @return integer
     */
    public function getDiscountRate()
    {
        return $this->discountRate ?: 0;
    }

    /**
     * Set reprofilingOption
     *
     * @param integer $reprofilingOption
     *
     * @return Customers
     */
    public function setReprofilingOption($reprofilingOption)
    {
        $this->reprofilingOption = $reprofilingOption;

        return $this;
    }

    /**
     * Get reprofilingOption
     *
     * @return integer
     */
    public function getReprofilingOption()
    {
        return $this->reprofilingOption;
    }

    /**
     * Set profilingCounter
     *
     * @param integer $profilingCounter
     *
     * @return Customers
     */
    public function setProfilingCounter($profilingCounter)
    {
        $this->profilingCounter = $profilingCounter;

        return $this;
    }

    /**
     * Get profilingCounter
     *
     * @return integer
     */
    public function getProfilingCounter()
    {
        return $this->profilingCounter;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return Customers
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set goldList
     *
     * @param boolean $goldList
     *
     * @return Customers
     */
    public function setGoldList($goldList)
    {
        $this->goldList = $goldList;

        return $this;
    }

    /**
     * Get goldList
     *
     * @return boolean
     */
    public function getGoldList()
    {
        return $this->goldList;
    }

    /**
     * Set maintainer
     *
     * @param boolean $maintainer
     *
     * @return Customers
     */
    public function setMaintainer($maintainer)
    {
        $this->maintainer = $maintainer;

        return $this;
    }

    /**
     * Get maintainer
     *
     * @return boolean
     */
    public function getMaintainer()
    {
        return $this->maintainer;
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
     * Set card
     *
     * @param \SharengoCore\Entity\Cards $card
     *
     * @return Customers
     */
    public function setCard(\SharengoCore\Entity\Cards $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return \SharengoCore\Entity\Cards
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
	 * Get list of customer bonuses
	 *
	 * @return Array of Doctrine Entities
	 */
    public function getBonuses()
    {
        return $this->customersbonuses;
    }

    public function getValidBonuses()
    {
        $validBonuses = [];

        foreach ($this->getBonuses() as $bonus) {
            if ($bonus->getActive() &&
                (null == $bonus->getValidFrom() || $bonus->getValidFrom() <= new \DateTime()) &&
                (null == $bonus->getValidTo() || $bonus->getValidTo() >= new \DateTime())) {
                $validBonuses[] = $bonus;
            }
        }

        return $validBonuses;

    }

    public function getTotalBonuses()
    {
        $total = 0;
        foreach ($this->getValidBonuses() as $bonus) {
            $total += $bonus->getTotal();
        }

        return $total;

    }

    public function getResidualBonuses()
    {
        $total = 0;
        foreach ($this->getValidBonuses() as $bonus) {
            $total += $bonus->getResidual();
        }

        return $total;

    }

    public function getUsedBonuses()
    {
        return $this->getTotalBonuses() - $this->getResidualBonuses();
    }

    /**
     * @return boolean
     */
    public function getGeneralCondition1()
    {
        return $this->generalCondition1;
    }

    /**
     * @param boolean $generalCondition1
     * @return Customers
     */
    public function setGeneralCondition1($generalCondition1)
    {
        $this->generalCondition1 = $generalCondition1;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getGeneralCondition2()
    {
        return $this->generalCondition2;
    }

    /**
     * @param boolean $generalCondition2
     * @return Customers
     */
    public function setGeneralCondition2($generalCondition2)
    {
        $this->generalCondition2 = $generalCondition2;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRegulationCondition1()
    {
        return $this->regulationCondition1;
    }

    /**
     * @param boolean $regulationCondition1
     * @return Customers
     */
    public function setRegulationCondition1($regulationCondition1)
    {
        $this->regulationCondition1 = $regulationCondition1;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRegulationCondition2()
    {
        return $this->regulationCondition2;
    }

    /**
     * @param boolean $regulationCondition2
     * @return Customers
     */
    public function setRegulationCondition2($regulationCondition2)
    {
        $this->regulationCondition2 = $regulationCondition2;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPrivacyCondition()
    {
        return $this->privacyCondition;
    }

    /**
     * @param boolean $privacyCondition
     * @return Customers
     */
    public function setPrivacyCondition($privacyCondition)
    {
        $this->privacyCondition = $privacyCondition;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPaymentAble()
    {
        return $this->paymentAble;
    }

    /**
     * @param boolean $paymentAble
     * @return Customers
     */
    public function setPaymentAble($paymentAble)
    {
        $this->paymentAble = $paymentAble;

        return $this;
    }

    /**
     * @return Customers
     */
    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * @return Customers
     */
    public function enable()
    {
        $this->enabled = true;

        return $this;
    }
}

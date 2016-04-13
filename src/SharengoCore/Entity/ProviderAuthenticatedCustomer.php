<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Hybrid_User_Profile as UserProfile;

/**
 * ProviderAuthenticatedCustomer
 *
 * @ORM\Table(name="provider_authenticated_customers")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ProviderAuthenticatedCustomersRepository")
 */
final class ProviderAuthenticatedCustomer
{
    /**
     * @var Uuid
     *
     * @ORM\Column(name="id", type="uuid", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="provider", type="string", nullable=false)
     */
    private $provider;

    /**
     * The Unique user's ID on the connected provider
     * @var mixed
     *
     * @ORM\Column(name="identifier", type="string", nullable=true)
     */
    private $identifier = null;

    /**
     * User website, blog, web page
     * @var string
     *
     * @ORM\Column(name="website_url", type="string", nullable=true)
     */
    private $webSiteURL = null;

    /**
     * URL link to profile page on the IDp web site
     * @var string
     *
     * @ORM\Column(name="profile_url", type="string", nullable=true)
     */
    private $profileURL = null;

    /**
     * URL link to user photo or avatar
     * @var string
     *
     * @ORM\Column(name="photo_url", type="string", nullable=true)
     */
    private $photoURL = null;

    /**
     * User displayName provided by the IDp or a concatenation of first and last name.
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", nullable=true)
     */
    private $displayName = null;

    /**
     * A short about_me
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description = null;

    /**
     * User's first name
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    private $firstName = null;

    /**
     * User's last name
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", nullable=true)
     */
    private $lastName = null;

    /**
     * Male or female
     * @var string
     *
     * @ORM\Column(name="gender", type="string", nullable=true)
     */
    private $gender = null;

    /**
     * Language
     * @var string
     *
     * @ORM\Column(name="language", type="string", nullable=true)
     */
    private $language = null;

    /**
     * User age, we don't calculate it. we return it as is if the IDp provide it.
     * @var int
     *
     * @ORM\Column(name="age", type="integer", nullable=true)
     */
    private $age = null;

    /**
     * User birth Day
     * @var int
     *
     * @ORM\Column(name="birth_day", type="integer", nullable=true)
     */
    private $birthDay = null;

    /**
     * User birth Month
     * @var int
     *
     * @ORM\Column(name="birth_month", type="integer", nullable=true)
     */
    private $birthMonth = null;

    /**
     * User birth Year
     * @var int
     *
     * @ORM\Column(name="birth_year", type="integer", nullable=true)
     */
    private $birthYear = null;

    /**
     * User email. Note: not all of IDp grant access to the user email
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email = null;

    /**
     * Verified user email. Note: not all of IDp grant access to verified user email
     * @var string
     *
     * @ORM\Column(name="email_verified", type="string", nullable=true)
     */
    private $emailVerified = null;

    /**
     * Phone number
     * @var string
     *
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    private $phone = null;

    /**
     * Complete user address
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    private $address = null;

    /**
     * User country
     * @var string
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    private $country = null;

    /**
     * Region
     * @var string
     *
     * @ORM\Column(name="region", type="string", nullable=true)
     */
    private $region = null;

    /**
     * City
     * @var string
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    private $city = null;

    /**
     * Postal code
     * @var string
     *
     * @ORM\Column(name="zip", type="string", nullable=true)
     */
    private $zip = null;

    /**
     * Customer who registered after authenticating with this profile
     * @var Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $customer = null;

    /**
     * Inserted timestamp
     * @var DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    private function __construct(
        $provider,
        $identifier,
        $webSiteURL,
        $profileURL,
        $photoURL,
        $displayName,
        $description,
        $firstName,
        $lastName,
        $gender,
        $language,
        $age,
        $birthDay,
        $birthMonth,
        $birthYear,
        $email,
        $emailVerified,
        $phone,
        $address,
        $country,
        $region,
        $city,
        $zip
    ) {
        $this->id = Uuid::uuid4();
        $this->provider = $provider;
        $this->identifier = $identifier;
        $this->webSiteURL = $webSiteURL;
        $this->profileURL = $profileURL;
        $this->photoURL = $photoURL;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->language = $language;
        $this->age = $age;
        $this->birthDay = $birthDay;
        $this->birthMonth = $birthMonth;
        $this->birthYear = $birthYear;
        $this->email = $email;
        $this->emailVerified = $emailVerified;
        $this->phone = $phone;
        $this->address = $address;
        $this->country = $country;
        $this->region = $region;
        $this->city = $city;
        $this->zip = $zip;
        $this->insertedTs = date_create();
    }

    /**
     * @param string $provider
     * @param UserProfile $userProfile
     * @return self
     */
    public static function fromUserProfile($provider, UserProfile $userProfile)
    {
        return new self(
            $provider,
            $userProfile->identifier,
            $userProfile->webSiteURL,
            $userProfile->profileURL,
            $userProfile->photoURL,
            $userProfile->displayName,
            $userProfile->description,
            $userProfile->firstName,
            $userProfile->lastName,
            $userProfile->gender,
            $userProfile->language,
            $userProfile->age,
            $userProfile->birthDay,
            $userProfile->birthMonth,
            $userProfile->birthYear,
            $userProfile->email,
            $userProfile->emailVerified,
            $userProfile->phone,
            $userProfile->address,
            $userProfile->country,
            $userProfile->region,
            $userProfile->city,
            $userProfile->zip
        );
    }

    /**
     * @return bool
     */
    public function hasEmail()
    {
        return !empty($this->emailVerified) || !empty($mail);
    }

    /**
     * if the customer has a verified email, returns that. Otherwise returns the
     * email, which could be empty.
     * To check if the user has an email use the `hasEmail` method
     *
     * @return string|null
     */
    public function email()
    {
        if (!empty($this->emailVerified)) {
            return $this->emailVerified;
        }

        return $this->email;
    }

    public function __call($name, $arguments)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new \BadMethodCallException();
    }

    public function linkCustomer(Customers $customer)
    {
        $this->customer = $customer;
    }
}

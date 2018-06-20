<?php

namespace SharengoCore\Service\Partner;
use Zend\EventManager\EventManager;

use SharengoCore\Service\CustomersService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\FleetService;
use SharengoCore\Service\UserEventsService;
use SharengoCore\Service\DriversLicenseValidationService;
use SharengoCore\Service\CountriesService;
use MvLabsDriversLicenseValidation\Service\PortaleAutomobilistaValidationService;

use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Entity\Repository\PartnersRepository;
use SharengoCore\Entity\Repository\ProvincesRepository;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Partners;
use SharengoCore\Entity\PartnersCustomers;
use SharengoCore\Entity\CustomerDeactivation;

use Cartasi\Entity\Contracts;
use Cartasi\Entity\Transactions;

use Doctrine\ORM\EntityManager;

class NugoService
{

    const PAYMENT_LABEL = 'NUGOPAY';

    /*
     * @var string
     */
    private $partnerName = 'nugo';

    /*
     * @var EntityManager
     */
    private $entityManager;

    /*
     * @var CustomersRepository
     */
    private $customersRepository;

    /*
     * @var CustomersRepository
     */
    private $partnersRepository;

    /*
     * @var CustomerService
     */
    private $customersService;

    /*
     * @var DeactivationService
     */
    private $deactivationService;

    /*
     * @var FleetService
     */
    private $fleetService;

    /*
     * @var ProvincesRepository
     */
    private $provincesRepository;

    /*
     * @var UserEventsService
     */
    private $userEventsService;

    /*
     * @var CountriesService
     */
    private $countriesService;

    /*
     * @var DriversLicenseValidationService
     */
    private $driversLicenseValidationService;

    /*
     * @var PortaleAutomobilistaValidationService
     */
    private $portaleAutomobilistaValidationService;

    public function __construct(
        EntityManager $entityManager,
        CustomersRepository $customersRepository,
        PartnersRepository $partnersRepository,
        CustomersService $customersService,
        CustomerDeactivationService $deactivationService,
        FleetService $fleetService,
        ProvincesRepository $provincesRepository,
        UserEventsService $userEventsService,
        CountriesService $countriesService,
        DriversLicenseValidationService $driversLicenseValidationService,
        PortaleAutomobilistaValidationService $portaleAutomobilistaValidationService
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $customersRepository;
        $this->partnersRepository = $partnersRepository;
        $this->customersService = $customersService;
        $this->deactivationService = $deactivationService;
        $this->fleetService = $fleetService;
        $this->provincesRepository = $provincesRepository;
        $this->userEventsService = $userEventsService;
        $this->countriesService = $countriesService;
        $this->driversLicenseValidationService = $driversLicenseValidationService;
        $this->portaleAutomobilistaValidationService = $portaleAutomobilistaValidationService;
    }

    /**
     *
     * @return string
     */
    public function getPartnerName() {
        return $this->partnerName;
    }

    /**
     * Signup for customer.
     *
     * @param Partners $partner
     * @param array $contentArray
     * @param array $partnerResponse
     * @return int
     */
    public function signup(Partners $partner, $contentArray, &$partnerResponse) {
        $partnerResponse = null;
        $isCustomerNew = false;
        $response = 200;

        if ($this->validateAndFormat($contentArray, $partnerResponse)) {

            $customer = $this->findCustomerByMainFields(
                $contentArray['email'],
                $contentArray['fiscalCode'],
                $contentArray['drivingLicense']['number']);

            if(is_null($customer) || $this->partnersRepository->isBelongCustomerPartner($partner, $customer)) { // is a new customer or exist and belong to partner

                if ($this->saveCustomer($partner, $contentArray, $customer, $isCustomerNew)) {
                    $partnerResponse = array(
                        "created" => $isCustomerNew,
                        "enabled" => $customer->getEnabled(),
                        "userId" => $customer->getId(),
                        "password" => $customer->getPassword(),
                        "pin" => $customer->getPrimaryPin()
                    );
                } else {
                    $partnerResponse = array(
                        "uri" => "partner/signup",
                        "status" => 401,
                        "statusFromProvider" => false,
                        "message" => "insert/update fail"
                    );
                }

            } else {    // customer alread exist and NOT belong to partner
                $partnerResponse = array(
                    "uri" => "partner/signup",
                    "status" => 401,
                    "statusFromProvider" => false,
                    "message" => "customer not belog to partner"
                );
            }

        } else {
            $response = 404;
        }

        return $response;
    }

     /**
     * Check the Json data match with the constarints
     *
     * @param array $contentArray
     * @param array $response
     * @return boolean
     */
    private function validateAndFormat(&$contentArray, &$response) {
        $strError = "";
        $errorArray = array();

        try {
            $key = 'partnerName';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if ($this->partnerName == $value) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'gender';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if ($value == 'm') {
                $value = 'male';
            }
            if ($value == 'f') {
                $value = 'female';
            }
            if ($value == 'male' || $value == 'female') {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'firstName';  // name
            $value = $this->getDataFormatedLower($contentArray, $key, FALSE);
            if (strlen($value) >= 3) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'lastName';   // surname
            $value = $this->getDataFormatedLower($contentArray, $key, FALSE);
            if (strlen($value) >= 3) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'birthDate';
            $value = $this->getDataFormatedDateTime($contentArray, $key);
            if (!is_null($value)) {
                $validator = new \Application\Form\Validator\EighteenDate();
                if($validator->isValid($value)) {

                } else {
                    $strError .= sprintf('Invalid %s ', $key);
                    array_push($errorArray, $key);
                }
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'birthCity'; // birthTown
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (strlen($value) > 0) {
                $contentArray[$key] = strtoupper($value);
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'birthProvince';
            $value = $this->getDataFormatedLower($contentArray, $key);
            $province = $this->provincesRepository->findOneBy(array('code' => strtoupper($value)));
            if (!is_null($province)) {
                $contentArray[$key] = $province->getCode();
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'birthCountry';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (strlen($value) == 2) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'fiscalCode';    //TODO: additional check
            $value = $this->getDataFormatedLower($contentArray, $key);
            //$validator = new \Application\Form\Validator\TaxCode();
            $validator = new \Application\Form\Validator\TaxCodeSignup();
            if ($validator->isValid($value)) {
                $contentArray[$key] = strtoupper($value);
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'vat';
            $value = $this->getDataFormatedLower($contentArray, $key);
            $value = strtoupper($value);
            if(strlen($value)>0){
                $validator = new \Application\Form\Validator\VatNumber();
                if ($validator->isValid($value)) {
                    $contentArray[$key] = $value;
                } else {
                    $strError .= sprintf('Invalid %s ', $key);
                    array_push($errorArray, $key);
                }
            } else {
                $contentArray[$key] = $value;
            }

            $key = 'phone';
            $value = $this->getDataFormatedLower($contentArray, $key);
            $contentArray[$key] = $value;

            $key = 'mobile'; //TODO: additional check
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (strlen($value) > 0) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'email'; //TODO: additional check
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'password';
            $value = $this->getDataFormatedLower($contentArray, $key, false);
            if ($this->isValidMd5($value)) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'pin';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (strlen($value) == 4 && is_numeric($value)) {
                $contentArray[$key] = $value;
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'address';
            if (isset($contentArray[$key])) {
                $address = $contentArray[$key];
                $key2 = 'street';
                $value = $this->getDataFormatedLower($address, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray[$key][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'city';     //town
                $value = $this->getDataFormatedLower($address, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray['address'][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'zip';
                $value = $this->getDataFormatedLower($address, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray['address'][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'province';
                $value = $this->getDataFormatedLower($address, $key2);
                $province = $this->provincesRepository->findOneBy(array('code' => strtoupper($value)));
                if (!is_null($province)) {
                    $contentArray['address'][$key2] = $province->getCode();
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'country';
                $value = $this->getDataFormatedLower($address, $key2);
                if (strlen($value) == 2) {
                    $contentArray['address'][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'drivingLicense';
            if (isset($contentArray[$key])) {
                $drivingLicense = $contentArray[$key];

                $key2 = 'number';
                $value = $this->getDataFormatedLower($drivingLicense, $key2);
                if (strlen($value) > 0) {
                    $contentArray[$key][$key2] = strtoupper($value);
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'country';
                $value = $this->getDataFormatedLower($drivingLicense, $key2);
                if (strlen($value) == 2) {
                    $contentArray[$key][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'city';     //town
                $value = $this->getDataFormatedLower($drivingLicense, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray[$key][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'issuedBy';
                $value = $this->getDataFormatedLower($drivingLicense, $key2);
                if ($value == 'dtt' || $value == 'mc' || $value == 'co' || $value == 'ae' || $value == 'uco' || $value == 'pre') {
                    $contentArray[$key][$key2] = strtoupper($value);
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'issueDate';
                $value = $this->getDataFormatedDateTime($drivingLicense, $key2);
                if (!is_null($value)) {

                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'expirationDate';
                $value = $this->getDataFormatedDateTime($drivingLicense, $key2);
                if (!is_null($value)) {

                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'firstName';    // firstname
                $value = $this->getDataFormatedLower($drivingLicense, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray[$key][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'lastName';      // surname
                $value = $this->getDataFormatedLower($drivingLicense, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray[$key][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'category';
                $value = $this->getDataFormatedLower($drivingLicense, $key2, FALSE);
                if (strlen($value) > 0) {
                    $contentArray[$key][$key2] = strtoupper($value);
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                $key2 = 'foreign';
                $value = $this->getDataFormatedLower($drivingLicense, $key2);
                if (is_bool($value)) {

                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                if ($drivingLicense["foreign"]) {
                    if($drivingLicense["country"]=='IT') {
                        $strError .= sprintf('Mismatch %s.%s ', 'foreign', 'country');
                        array_push($errorArray, $key.'.'.$key2);
                    }
                } else {
                    if($drivingLicense["country"]!='IT') {
                        $strError .= sprintf('Mismatch %s.%s ', 'foreign', 'country');
                        array_push($errorArray, $key.'.'.$key2);
                    }
                }

            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'generalCondition1';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {

            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'generalCondition2';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {

            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'privacyCondition';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {

            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'privacyInformation';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {

            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            if ($strError == '') {
                $result = true;
                $response = null;
            } else {
                $result = false;
                $response = array(
                    "uri" => "partner/signup",
                    "status" => 401,
                    "statusFromProvider" => false,
                    "message" => $strError,
                    "error" => json_encode($errorArray)
                );
            }
        } catch (\Exception $ex) {
            $result = false;
            $response = array(
                "uri" => "partner/signup",
                "status" => 401,
                "statusFromProvider" => false,
                "message" => $ex->getMessage(),
            );
        }

        return $result;
    }

    private function getDataFormatedLower(array $contentArray, $keyValue, $toLower = true) {
        $result = "";

        if (isset($contentArray[$keyValue])) {
            if(is_bool($contentArray[$keyValue])) {
                $result = $contentArray[$keyValue];
            } else {
                if ($toLower) {
                    $result = trim(strtolower($contentArray[$keyValue]));
                } else {
                    $result = trim($contentArray[$keyValue]);
                }
            }
        }
        return $result;
    }

    private function getDataFormatedDateTime(array $contentArray, $keyValue) {
        $result = null;

        if (isset($contentArray[$keyValue])) {
            if (is_array($contentArray[$keyValue])) {
                if (count($contentArray[$keyValue]) == 3) {
                    if (checkdate($contentArray[$keyValue][1], $contentArray[$keyValue][2], $contentArray[$keyValue][0])) {
                        $result = date('Y-m-d', strtotime(sprintf('%d-%d-%d', $contentArray[$keyValue][0], $contentArray[$keyValue][1], $contentArray[$keyValue][2])));
                    }
                }
            }
        }
        return $result;
    }

     /**
     * Find a customer tha match email or tax code or driver license
     *
     * @param string $email
     * @param string $taxCode
     * @param string $driverLicense
     * @return Customers
     */
    public function findCustomerByMainFields($email, $taxCode, $driverLicense)
    {

        $customers2 = $this->customersRepository->findByCI("taxCode", $taxCode);
        if(!empty($customers2)){
            return $customers2[0];
        }

        $customers3 = $this->customersRepository->findByCI("driverLicense", $driverLicense);
        if(!empty($customers3)){
            return $customers3[0];
        }

        $customers = $this->customersRepository->findByCI("email", $email);
        if(!empty($customers)){
            return $customers[0];
        }

        return null;
    }

    /**
     *
     * @param string $md5
     * @return boolean
     */
    private function isValidMd5($md5 ='') {
        return strlen($md5) == 32 && ctype_xdigit($md5);
    }

//    /**
//     * Insert a new customer
//     *
//     * @param Partners $partner
//     * @param type $data
//     * @return type
//     * @throws \Exception
//     */
//    private function saveNewCustomer(Partners $partner, $data)
//    {
//        $result = null;
//
//        $this->entityManager->getConnection()->beginTransaction();
//        try {
//            // set anagraphic data
//            $customer = new Customers();
//            $customer->setInsertedTs(date_create());
//            //$customer->setPartner($data['partnerName']);
//            $customer->setGender($data['gender']);
//            $customer->setSurname($data['lastName']);
//            $customer->setName($data['firstName']);
//            //$customer->setBirthDate(new \DateTime(sprintf('%s-%s-%s 00:00:00',$data['birthDate'][0], $data['birthDate'][1], $data['birthDate'][2])));
//            $customer->setBirthDate(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['birthDate']))));
//
//            $customer->setBirthTown($data['birthCity']);
//            $customer->setBirthProvince($data['birthProvince']);
//            $customer->setBirthCountry($data['birthCountry']);
//
//            $customer->setTaxCode($data['fiscalCode']);     //NB tax code
//            $customer->setVat($data['vat']);
//            $customer->setPhone($data['phone']);
//            $customer->setMobile($data['mobile']);
//            $customer->setFax('');
//            $customer->setEmail($data['email']);
//            $customer->setPassword($data['password']);
//
//            $pins = ['primary' => $data['pin']];
//            $customer->setPin(json_encode($pins));
//
//            $customer->setAddress($data['address']['street']);
//            $customer->setAddressInfo('');
//            $customer->setTown($data['address']['city']);
//            $customer->setZipCode($data['address']['zip']);
//            $customer->setProvince($data['address']['province']);
//            $customer->setCountry($data['address']['country']);
//
//            $customer->setDriverLicense($data['drivingLicense']['number']);
//            $customer->setDriverLicenseCountry($data['drivingLicense']['country']);
//            $customer->setDriverLicenseAuthority($data['drivingLicense']['issuedBy']);
//            $customer->setDriverLicenseReleaseDate(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['drivingLicense']['issueDate']))));
//            $customer->setDriverLicenseExpire(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['drivingLicense']['expirationDate']))));
//            $customer->setDriverLicenseName($data['drivingLicense']['firstName']);
//            $customer->setDriverLicenseSurname($data['drivingLicense']['lastName']);
//            $customer->setDriverLicenseCategories($data['drivingLicense']['category']);
//            $customer->setDriverLicenseForeign($data['drivingLicense']['foreign']);
//
//            $customer->setGeneralCondition1($data['generalCondition1']);
//            $customer->setGeneralCondition2($data['generalCondition2']);
//            $customer->setPrivacyCondition($data['privacyCondition']);
//            $customer->setPrivacyInformation($data['privacyInformation']);
//
//            // set backend data
//            $hash = hash("MD5", strtoupper($data['email']).strtoupper($data['password']));
//            $customer->setHash($hash);
//
////            $customer->setEnabled(true);
//            $customer->setFirstPaymentCompleted(true);
//            $customer->setRegistrationCompleted(true);
//            $customer->setDiscountRate(0);
//            $customer->setPaymentAble(true);
//            $customer->setFleet($this->fleetService->getFleetById(1));         // default Milano
//            $customer->setLanguage('it');
//            $customer->setMaintainer(false);
//            $customer->setGoldList(false);
//
////            $customer->setProfilingCounter(0);
////            $customer->setReprofilingOption(0);
//
//            $this->entityManager->persist($customer);
//            $this->entityManager->flush();
//
//            $this->customersService->assignCard($customer);
//
//            //$result = $this->customersService->getUserFromHash($hash);  //TODO: improve
//            $this->newPartnersCustomers($partner, $customer);
//            $contract = $this->newContract($partner, $customer);
//
//            $this->newTransaction($contract, 0, 'EUR', self::PAYMENT_LABEL, strtoupper($this->partnerName).'+'.self::PAYMENT_LABEL.'+PREPAID+-+-N', true);
//            $this->newDriverLicenseDirectValidation($customer, $data['drivingLicense']);
//
////
////            $this->newDriverLicenseValidation($customer, $data['drivingLicense']);
////            $this->newCustomerDeactivations($customer,  $data['drivingLicense']);
//
//
//            $result = $customer;
//            $this->entityManager->getConnection()->commit();
//
//        } catch (\Exception $e) {
//            $this->entityManager->getConnection()->rollback();
//        }
//
//        return $result;
//    }

    /**
     * 
     * @param Partners $partner
     * @param array $data
     * @param type  $customer can be a Customers or null
     * @param boolean $isCustomerNew
     * @return boolean
     */
    private function saveCustomer(Partners $partner, $data, &$customer,  &$isCustomerNew = false) {
        $result = false;
        $isCustomerNew = false;

        try {
            if(is_null($customer)) {
                $isCustomerNew = true;
                $customer = new Customers();
                $customer->setInsertedTs(date_create());
            }

            $customer->setGender($data['gender']);
            $customer->setSurname($data['lastName']);
            $customer->setName($data['firstName']);
            //$customer->setBirthDate(new \DateTime(sprintf('%s-%s-%s 00:00:00',$data['birthDate'][0], $data['birthDate'][1], $data['birthDate'][2])));
            $customer->setBirthDate(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['birthDate']))));

            $customer->setBirthTown($data['birthCity']);
            $customer->setBirthProvince($data['birthProvince']);
            $customer->setBirthCountry($data['birthCountry']);

            $customer->setTaxCode($data['fiscalCode']);     //NB tax code
            $customer->setVat($data['vat']);
            $customer->setPhone($data['phone']);
            $customer->setMobile($data['mobile']);
            $customer->setFax('');
            $customer->setEmail($data['email']);
            $customer->setPassword($data['password']);

            $pins = ['primary' => $data['pin']];
            $customer->setPin(json_encode($pins));

            $customer->setAddress($data['address']['street']);
            $customer->setAddressInfo('');
            $customer->setTown($data['address']['city']);
            $customer->setZipCode($data['address']['zip']);
            $customer->setProvince($data['address']['province']);
            $customer->setCountry($data['address']['country']);

            $customer->setDriverLicense($data['drivingLicense']['number']);
            $customer->setDriverLicenseCountry($data['drivingLicense']['country']);
            $customer->setDriverLicenseAuthority($data['drivingLicense']['issuedBy']);
            $customer->setDriverLicenseReleaseDate(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['drivingLicense']['issueDate']))));
            $customer->setDriverLicenseExpire(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['drivingLicense']['expirationDate']))));
            $customer->setDriverLicenseName($data['drivingLicense']['firstName']);
            $customer->setDriverLicenseSurname($data['drivingLicense']['lastName']);
            $customer->setDriverLicenseCategories($data['drivingLicense']['category']);
            $customer->setDriverLicenseForeign($data['drivingLicense']['foreign']);

            $customer->setGeneralCondition1($data['generalCondition1']);
            $customer->setGeneralCondition2($data['generalCondition2']);
            $customer->setPrivacyCondition($data['privacyCondition']);
            $customer->setPrivacyInformation($data['privacyInformation']);

            // set backend data
            $hash = hash("MD5", strtoupper($data['email']).strtoupper($data['password']));
            $customer->setHash($hash);

//            $customer->setEnabled(true);
            $customer->setFirstPaymentCompleted(true);
            $customer->setRegistrationCompleted(true);
            $customer->setDiscountRate(0);
            $customer->setPaymentAble(true);
            $customer->setFleet($this->fleetService->getFleetById(1));         // default Milano
            $customer->setLanguage('it');
            $customer->setMaintainer(false);
            $customer->setGoldList(false);

            $this->entityManager->persist($customer);
            $this->entityManager->flush();

            //$result = $this->customersService->getUserFromHash($hash);  //TODO: improve
            if($isCustomerNew) {
                $this->customersService->assignCard($customer);
                $this->newPartnersCustomers($partner, $customer);
                $contract = $this->newContract($partner, $customer);

                $this->newTransaction($contract, 0, 'EUR', self::PAYMENT_LABEL, strtoupper($this->partnerName).'+'.self::PAYMENT_LABEL.'+PREPAID+-+-N', true);
            }

            $this->newDriverLicenseDirectValidation($customer, $data['drivingLicense']);

            $result = true;

        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();
        }

        return $result;

    }

    /**
     * Create a new Partner-Customer row
     *
     * @param Partners $partner
     * @param Customers $customer
     * @return PartnersCustomers
     */
    private function newPartnersCustomers(Partners $partner, Customers $customer ) {

        $partnersCustomers = new PartnersCustomers();
        $partnersCustomers->setPartner($partner);
        $partnersCustomers->setCustomer($customer);

        $this->entityManager->persist($partnersCustomers);
        $this->entityManager->flush();

        return $partnersCustomers;
    }

     /**
     * Create a new contract
     *
     * @param Partners $partner
     * @param Customers $customer
     * @return Contracts
     */
    private function newContract(Partners $partner, Customers $customer) {

        $contract = new Contracts();
        $contract->setCustomer($customer);
        $contract->setParter($partner);

        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return $contract;
    }

    /**
     * Create a new transiction.
     *
     * @param Contracts $contract
     * @param int $amount
     * @param string $currency
     * @param string $brand
     * @param string $productType
     * @param bool $isFirstPayment
     * @return Transactions
     */
    private function newTransaction(Contracts $contract, $amount, $currency, $brand, $productType, $isFirstPayment) {

        $transaction = new Transactions();
        $transaction->setContract($contract);
        $transaction->setEmail($contract->getCustomer()->getEmail());
        $transaction->setAmount($amount);
        $transaction->setCurrency($currency);
        $transaction->setName($contract->getCustomer()->getName());
        $transaction->setSurname($contract->getCustomer()->getSurname());
        $transaction->setBrand($brand);

        $transaction->setOutcome('OK');
        $transaction->setDatetime(date_create());
        $transaction->setMessage('Message OK');
        $transaction->setRegion('EUROPE');
        $transaction->setCountry('ITA');
        $transaction->setProductType($productType);
        $transaction->setIsFirstPayment($isFirstPayment);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }


    /**
     *
     * @param Customers $customer
     * @param string $drivingLicense
     * @return Response
     */
    private function newDriverLicenseDirectValidation(Customers $customer, $drivingLicense) {

        $response = null;

        if(!$this->deactivationService->hasActiveDeactivations($customer, CustomerDeactivation::INVALID_DRIVERS_LICENSE)) {
            $this->deactivationService->deactivateForDriversLicense($customer);
        }
        
//        $details = array('deactivation' => $drivingLicense);
//        $customerDeactivations = new CustomerDeactivation($customer, CustomerDeactivation::INVALID_DRIVERS_LICENSE, $details);
//        $this->entityManager->persist($customerDeactivations);
//        $this->entityManager->flush();

        $data = [
            'email' => $customer->getEmail(),
            'driverLicense' => $customer->getDriverLicense(),
            'taxCode' => $customer->getTaxCode(),
            'driverLicenseName' => $customer->getDriverLicenseName(),
            'driverLicenseSurname' => $customer->getDriverLicenseSurname(),
            'birthDate' => ['date' => $customer->getBirthDate()->format('Y-m-d')],
            'birthCountry' => $customer->getBirthCountry(),
            'birthProvince' => $customer->getBirthProvince(),
            'birthTown' => $customer->getBirthTown()
        ];

        $data['birthCountryMCTC'] = $this->countriesService->getMctcCode($data['birthCountry']);
        $data['birthProvince'] = $this->driversLicenseValidationService->changeProvinceForValidationDriverLicense($data);

        $response = $this->portaleAutomobilistaValidationService->validateDriversLicense($data);
        $this->driversLicenseValidationService->addFromResponse($customer, $response, $data);
        if ($response->valid()) {
            $this->deactivationService->reactivateCustomerForDriversLicense($customer);

//            $details = array('reactivation' => $drivingLicense,);
//            $customerDeactivations->reactivate($details, date_create(), null);
//            $this->entityManager->persist($customerDeactivations);
//            $this->entityManager->flush();

            $customer->setEnabled(true);

        } else {
            $customer->setEnabled(false);
        }

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $response;
    }
}


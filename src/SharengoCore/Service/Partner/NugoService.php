<?php

namespace SharengoCore\Service\Partner;
//use Zend\EventManager\EventManager;
use SharengoCore\Service\SimpleLoggerService as Logger;

use SharengoCore\Service\CustomersService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\FleetService;
use SharengoCore\Service\UserEventsService;
use SharengoCore\Service\DriversLicenseValidationService;
use SharengoCore\Service\CountriesService;
use SharengoCore\Service\InvoicesService;
use MvLabsDriversLicenseValidation\Service\PortaleAutomobilistaValidationService;

use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Entity\Repository\PartnersRepository;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Repository\ProvincesRepository;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Partners;
use SharengoCore\Entity\PartnersCustomers;
use SharengoCore\Entity\CustomerDeactivation;
use SharengoCore\Entity\Invoices;
use SharengoCore\Entity\Fleet;

use SharengoCore\Entity\TripPayments;

use Cartasi\Entity\Contracts;
use Cartasi\Entity\Transactions;

use Doctrine\ORM\EntityManager;

use Zend\Http\Request;
use Zend\Http\Client;

class NugoService
{

    const PAYMENT_LABEL = 'NUGOPAY';
    const INVOICE_HEADER_NOTE = '<br>Documento emesso da NUGO S.p.A. a nome e per conto di %s ';
    const TYPE_INVOICES = "Invoices";
    const TYPE_CUSTOMERS = "Customers";

    const NOTIFY_STATUS_CONFIRMED = "CONFIRMED";
    const NOTIFY_STATUS_CREATED = "CREATED";
    const NOTIFY_STATUS_DELETED = "DELETED";
    const NOTIFY_STATUS_REJECTED = "REJECTED";
    const NOTIFY_STATUS_DISABLED = "DISABLED";

    /**
     *
     * @var Logger logger
     */
    private $logger;

    /**
     *
     * @var [] $config
     */
    private $config;

    /**
     *
     * @var [] exportConfig
     */
    private $exportConfig;

    /**
     *
     * @var string
     */
    private $partnerName = 'nugo';

    /**
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     *
     * @var CustomersRepository
     */
    private $customersRepository;

    /**
     *
     * @var CustomersRepository
     */
    private $partnersRepository;

    /**
     *
     * @var TripsRepository
     */
    private $tripsRepository;

    /**
     *
     * @var CustomerService
     */
    private $customersService;

    /**
     *
     * @var DeactivationService
     */
    private $deactivationService;

    /**
     *
     * @var FleetService
     */
    private $fleetService;

    /**
     *
     * @var ProvincesRepository
     */
    private $provincesRepository;

    /**
     *
     * @var UserEventsService
     */
    private $userEventsService;

    /**
     *
     * @var CountriesService
     */
    private $countriesService;

    /**
     *
     * @var InvoicesService
     */
    private $invoicesService;

    /**
     *
     * @var DriversLicenseValidationService
     */
    private $driversLicenseValidationService;

    /**
     *
     * @var PortaleAutomobilistaValidationService
     */
    private $portaleAutomobilistaValidationService;

    /**
     *
     * @var Partner $partner
     */
    private $partner;

    /**
     *
     * @var array
     */
    private $params;

    /**
     *
     * @var HttpClient httpClient
     */
    private $httpClient;

    /**
     *
     * @var int ivaPercentage
     */
    private $ivaPercentage;

    /**
     *
     * @var int version
     */
    private $version;

    /**
     *
     * @var boolean dryRun
     */
    private $dryRun;

    /**
     *
     * @var boolean noFtp
     */
    private $noFtp;
    /**
     * Connection to ftp server
     * @var resource | null
     */
    private $ftpConn = null;

    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        $config,
        CustomersRepository $customersRepository,
        PartnersRepository $partnersRepository,
        TripsRepository $tripsRepository,
        CustomersService $customersService,
        CustomerDeactivationService $deactivationService,
        FleetService $fleetService,
        ProvincesRepository $provincesRepository,
        UserEventsService $userEventsService,
        CountriesService $countriesService,
        InvoicesService $invoicesService,
        DriversLicenseValidationService $driversLicenseValidationService,
        PortaleAutomobilistaValidationService $portaleAutomobilistaValidationService
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->config = $config;
        $this->customersRepository = $customersRepository;
        $this->partnersRepository = $partnersRepository;
        $this->tripsRepository = $tripsRepository;
        $this->customersService = $customersService;
        $this->deactivationService = $deactivationService;
        $this->fleetService = $fleetService;
        $this->provincesRepository = $provincesRepository;
        $this->userEventsService = $userEventsService;
        $this->countriesService = $countriesService;
        $this->invoicesService = $invoicesService;
        $this->driversLicenseValidationService = $driversLicenseValidationService;
        $this->portaleAutomobilistaValidationService = $portaleAutomobilistaValidationService;

        $this->exportConfig = $config['export'];
        $this->partner = $this->partnersRepository->findOneBy(array('code' => $this->partnerName, 'enabled' => true));
        $this->params = $this->partner->getParamsDecode();

        $this->httpClient = new Client();
        $this->httpClient->setMethod(Request::METHOD_GET);
        $this->httpClient->setOptions([
            'maxredirects' => 0,
            'timeout' => 90
        ]);

        $this->logger->setOutputEnvironment(Logger::OUTPUT_ON);
        $this->logger->setOutputType(Logger::TYPE_CONSOLE);

        $this->ivaPercentage = 22;
        $this->version = 4;
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
        $uri = "partner/signup";
        $statusFromProvider = false;

        if(!$this->isRemoteAddressValid()) {
            $response = 403;
            $partnerResponse = array(
                "uri" => $uri,
                "status" => $response,
                "statusFromProvider" => $statusFromProvider,
                "message" => "forbidden for " . \SharengoCore\Service\PartnerService::getRemoteAddress()
            );
            return $response;
        }

        if ($this->validateAndFormat($contentArray, $partnerResponse)) {

            $customer = $this->findCustomerByMainFields(
                $contentArray['email'],
                $contentArray['fiscalCode'],
                $contentArray['mobile'],
                $contentArray['drivingLicense']['number']);

            if(is_null($customer) || $this->partnersRepository->isBelongCustomerPartner($partner, $customer)) { // is a new customer or exist and belong to partner

                if ($this->saveCustomer($partner, $contentArray, $customer, $partnerResponse)) {
                    $response = 200;
                } else {
                    $response = 400;
                    $partnerResponse = array(
                        "uri" => $uri,
                        "status" => $response,
                        "statusFromProvider" => $statusFromProvider,
                        "message" => "insert/update fail"
                    );
                }

            } else {    // customer alread exist and NOT belong to partner
                $this->notifyCustomerStatusRequestByCustomer($customer, self::NOTIFY_STATUS_REJECTED);
                $response = 403;
                $partnerResponse = array(
                    "uri" => $uri,
                    "status" => $response,
                    "statusFromProvider" => $statusFromProvider,
                    "message" => "customer not belong to partner"
                );
            }

        } else {
            if(isset($contentArray['email'])) {
                $this->notifyCustomerStatusRequestByEmail($contentArray['email'], self::NOTIFY_STATUS_REJECTED);
            }

            $response = 400;    // 400 Bad Request
        }

        return $response;
    }

    /**
     * Check if remote address is a valid ip.
     * 
     * @return boolean Return true if ip is valid
     */
    private function isRemoteAddressValid() {
        $result = true;

        try {
            $remoteAddress = \SharengoCore\Service\PartnerService::getRemoteAddress();

            if(isset($this->params['signup']['validIp'])){
                $listOfValidIp = trim($this->params['signup']['validIp']);
                if($listOfValidIp !== '') {
                    if(strpos($listOfValidIp, $remoteAddress) !== false){
                        $result = true;
                    } else {
                        $result = false;
                    }
                }
            }
        } catch (Exception $ex) {
        }
        return $result;
    }

    /**
     * Send a curl request to notity the new status.
     * 
     * @param Customers $customer
     * @return response
     */

    public function notifyCustomerStatus(Customers $customer) {
        $result = false;

        if($customer->getEnabled()) {
            $result = $this->notifyCustomerStatusRequestByCustomer($customer, self::NOTIFY_STATUS_CONFIRMED);
        } else {
            $result = $this->notifyCustomerStatusRequestByCustomer($customer, self::NOTIFY_STATUS_DISABLED);
        }

        return $result;
    }

    /**
     * 
     * @param Customers $customer
     * @param string $status
     * @return boolean
     */
    private function notifyCustomerStatusRequestByCustomer(Customers $customer, $status) {
        $result = false;

        if(!is_null($customer)) {
            $result = $this->notifyCustomerStatusRequestByEmail($customer->getEmail(), $status);
        }

        return $result;

    }

    /**
     * 
     * @param string $email
     * @param string $status
     * @return boolean
     */
    private function notifyCustomerStatusRequestByEmail($email, $status) {
        $result = false;

        try {
            if(is_null($email)) {
                return $result;
            }

            $json = json_encode(
                array(
                    'email' => $email,
                    'status' => $status
                )
            );
            //var_dump($json);

            $this->httpClient->setUri($this->params['notifyCustomerStatus']['uri']);
            $this->httpClient->setMethod(Request::METHOD_PUT);
            $adapter = new \Zend\Http\Client\Adapter\Curl();
            $this->httpClient->setAdapter($adapter);

            $adapter->setOptions(array(
                'curloptions' => array(
                    CURLOPT_SSLVERSION => 6, //tls1.2
                    //CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_VERBOSE => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0
                )
            ));

            $this->httpClient->setRawBody($json);
            $this->httpClient->setHeaders(
                array(
                    'Content-type' => 'application/json',
                    'charset' => 'UTF-8'
                )
            );

            $httpResponse = $this->httpClient->send();
            //$response = $httpResponse->getBody();
            //var_dump($httpResponse->getStatusCode());
            if($httpResponse->getStatusCode() == 204) {
                $result = true;
            }

        } catch (Exception $ex) {
            var_dump($ex);
        }
        return $result;

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

            
            $key = 'fleetId';  // fleetId
            $value = intval($this->getDataFormatedLower($contentArray, $key, FALSE));
            if($value>=1 && $value<=4) {
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
            $value = $this->getDataFormatedMobile($contentArray, $key);
            if (strlen($value) >= 9) {
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
            $value = $this->getDataFormatedLower($contentArray, $key, true);
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
                     $contentArray[$key][$key2] = $value;
                } else {
                    $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                    array_push($errorArray, $key.'.'.$key2);
                }

                if ($contentArray["drivingLicense"]["foreign"]) {
                    if($contentArray["drivingLicense"]["country"]=='it') {
                        $strError .= sprintf('Mismatch %s.%s ', 'foreign', 'country');
                        array_push($errorArray, $key.'.'.$key2);
                    }
                } else {
                    if($contentArray["drivingLicense"]["country"]!='it') {
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
                $contentArray[$key] = $value;
                if(!$value) {
                    $strError .= sprintf('No %s ', $key);
                    array_push($errorArray, $key);
                }
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'generalCondition2';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {
                $contentArray[$key] = $value;
                if(!$value) {
                    $strError .= sprintf('No %s ', $key);
                    array_push($errorArray, $key);
                }
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'privacyCondition';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {
                $contentArray[$key] = $value;
                if(!$value) {
                    $strError .= sprintf('No %s ', $key);
                    array_push($errorArray, $key);
                }
            } else {
                $strError .= sprintf('Invalid %s ', $key);
                array_push($errorArray, $key);
            }

            $key = 'privacyInformation';
            $value = $this->getDataFormatedLower($contentArray, $key);
            if (is_bool($value)) {
                $contentArray[$key] = $value;
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
                    "status" => 403,
                    "statusFromProvider" => false,
                    "message" => $strError,
                    "error" => $errorArray
                );
            }
        } catch (\Exception $ex) {
            $result = false;
            $response = array(
                "uri" => "partner/signup",
                "status" => 403,
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

        private function getDataDDMMYYYYFormatedDateTime($dateTime) {
        $result = null;

        $contentArray = explode("-", trim($dateTime));

        if (count($contentArray) == 3) {
            if (checkdate($contentArray[1], $contentArray[0], $contentArray[2])) {
                $result = date('Y-m-d', strtotime(sprintf('%d-%d-%d', $contentArray[2], $contentArray[1], $contentArray[0])));
            }
        }

        return $result;
    }

    private function getDataFormatedMobile(array $contentArray, $keyValue) {
        $result = null;

        if (isset($contentArray[$keyValue])) {
            $result = preg_replace('/[^0-9+]/', '', $contentArray[$keyValue]);
        }
         return $result;
    }

     /**
     * Find a customer tha match email or tax code or driver license
     *
     * @param string $email
     * @param string $taxCode
     * @param string $mobile
     * @param string $driverLicense
     * @return Customers
     */
    public function findCustomerByMainFields($email, $taxCode, $mobile, $driverLicense)
    {

        $customers = $this->customersRepository->findByCI("email", $email);
        if(!empty($customers)){
            return $customers[0];
        }

        $customers = $this->customersRepository->findByCI("taxCode", $taxCode);
        if(!empty($customers)){
            return $customers[0];
        }

        $customers = $this->customersRepository->findByMobileLast9Digit($mobile);
        if(!empty($customers)){
            return $customers[0];
        }

        $customers = $this->customersRepository->findByCI("driverLicense", $driverLicense);
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

    private function isValidMobile($mobile) {
        $result = false;
        if($this->customersRepository->checkMobileNumberLast9Digit($mobile)==0) {
            $result = true;
        }
        return $result;
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
     * @param Customers $customer
     * @param array $partnerResponse
     * @return boolean
     */
    private function saveCustomer(Partners $partner, $data, Customers $customer = null, &$partnerResponse) {
        $result = false;
        $disableReason = '';
        $isCustomerNew = false;

        try {
            if(is_null($customer)) {
                $isCustomerNew = true;
                $customer = new Customers();
                $customer->setInsertedTs(date_create());
            }

            $customer->setGender($data['gender']);
            $customer->setFleet($this->fleetService->getFleetById($data['fleetId']));

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

            $driverLicenseResponse = $this->newDriverLicenseDirectValidation($customer, $data['drivingLicense']);

            if(is_null($driverLicenseResponse)) {
                $driverLicenseMessage = "no service";
            } else {
                $driverLicenseMessage = $driverLicenseResponse->message();
            }

            $partnerResponse = array(
                "created" => $isCustomerNew,
                "enabled" => $customer->getEnabled(),
                "userId" => $customer->getId(),
                "email" => $customer->getEmail(),
                "password" => $customer->getPassword(),
                "pin" => $customer->getPrimaryPin(),
                "deactivationReasons" => $this->getCustomerDeactivationReason($customer),
                "driverLicenseResponse" => $driverLicenseMessage
            );

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

        try {
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
                $customer->setEnabled(true);
            } else {
                $customer->setEnabled(false);
            }

            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        } catch (Exception $ex) {

        }
        return $response;
    }

    /**
     * 
     * @param type $dryRun
     * @param \DateTime $date
     * @param type $fleetId
     * @return type
     */
    public function importInvoice($dryRun,\DateTime $date, $fleetId) {
        $response = null;

        try {

            $this->dryRun = $dryRun;
            $this->logger->log(sprintf("%s;INF;importInvoice;date=%s;fleetId=%s\n", 
               date_create()->format('y-m-d H:i:s'),
               $date->format('y-m-d'),
               $fleetId));

            $response =$this->invoiceRequest($date);

            if(is_array($response)) {
                foreach($response as $nugoInvoice) {
                    $nugoReferenceId = $nugoInvoice["referenceId"];
                    $nugoInvoiceNumber = $nugoInvoice["documentNumber"];
                    $nugoInvoiceDate = $nugoInvoice["invoiceDate"];
                    $this->invoceProcess($nugoReferenceId, $nugoInvoiceNumber, $nugoInvoiceDate);
                }
            }

            $this->logger->log(sprintf("%s;INF;importInvoice;end\n", 
               date_create()->format('y-m-d H:i:s')));

        } catch (Exception $ex) {
            $response= null;
        }
        return $response;
    }

    /**
     * Send a request for invoices.
     * @param \DateTime $date
     * @return type
     */
    private function invoiceRequest(\DateTime $date) {
        $result =null;

        $this->httpClient->setUri($this->params['importInvoice']['uri']);
        $this->httpClient->setMethod(Request::METHOD_GET);
        $adapter = new \Zend\Http\Client\Adapter\Curl();
        $this->httpClient->setAdapter($adapter);

        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_SSLVERSION => 6, //tls1.2
                //CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => 0,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            )
        ));

        $this->httpClient->setParameterGet(array('date' => $date->format('Y-m-d')));

        $httpResponse = $this->httpClient->send();
        $result = json_decode($httpResponse->getBody(), true);

        return $result;
    }
    
    private function invoceProcess($nugoReferenceId, $nugoInvoiceNumber, $nugoInvoiceDate) {
        $result = false;

        $nugoReferenceId = intval(trim($nugoReferenceId));
        $nugoInvoiceNumber = intval(trim($nugoInvoiceNumber));
        $nugoInvoiceDate = trim($nugoInvoiceDate);

        $invoiceDate = $this->getDataDDMMYYYYFormatedDateTime($nugoInvoiceDate);
        if(!is_null($invoiceDate)) {
            $trip = $this->tripsRepository->findOneById($nugoReferenceId);
            if (!is_null($trip)) {
                $tripPayment = $trip->getTripPayment();
                if (!is_null($tripPayment)) {
                    $partner = $tripPayment->getPartner();
                    if ($partner == $this->partner) {
                        if($tripPayment->getStatus() == TripPayments::STATUS_PAYED_CORRECTLY) {
                            $result = $this->invoceCreateForTrip($tripPayment, $nugoInvoiceNumber, $invoiceDate);
                        } else if ($tripPayment->getStatus() == TripPayments::STATUS_INVOICED) {
                            $result = $this->invoceUpdateForTrip($tripPayment, $nugoInvoiceNumber, $invoiceDate);
                        } else {
                            $this->logger->log(sprintf("%s;ERR;invoceProcess;%s;%s;%s;wrong status\n", 
                               date_create()->format('y-m-d H:i:s'),
                               $nugoReferenceId,
                               $nugoInvoiceNumber,
                               $nugoInvoiceDate));
                        }
                    } else {
                        $this->logger->log(sprintf("%s;ERR;invoceProcess;%s;%s;%s;wrong partner\n", 
                           date_create()->format('y-m-d H:i:s'),
                           $nugoReferenceId,
                           $nugoInvoiceNumber,
                           $nugoInvoiceDate));
                    }
                } else {
                    $this->logger->log(sprintf("%s;ERR;invoceProcess;%s;%s;%s;no trip payment\n", 
                       date_create()->format('y-m-d H:i:s'),
                       $nugoReferenceId,
                       $nugoInvoiceNumber,
                       $nugoInvoiceDate));
                }
            } else {
                $this->logger->log(sprintf("%s;ERR;invoceProcess;%s;%s;%s;no trip\n", 
                   date_create()->format('y-m-d H:i:s'),
                   $nugoReferenceId,
                   $nugoInvoiceNumber,
                   $nugoInvoiceDate));
            }
        }

        return $result;
    }

    /**
     * Insert a new invoice partner
     * 
     * @param TripPayments $tripPayment
     * @param string $nugoInvoiceNumber
     * @param string $invoiceDate
     * @return boolean
     */
    private function invoceCreateForTrip(TripPayments $tripPayment, $nugoInvoiceNumber, $invoiceDate) {
        $result = false;

        if($this->dryRun) {
            return $result;
        }

        $customer = $tripPayment->getCustomer();
        $fleet = $tripPayment->getTrip()->getFleet();

        $content = $this->getInvoiceContent($tripPayment, $nugoInvoiceNumber, $invoiceDate);

        $sql = sprintf("INSERT INTO invoices (id, invoice_number, customer_id, generated_ts, content, version, type, invoice_date, amount, iva, fleet_id, partner_id) " .
            "VALUES (nextval('invoices_id_seq'), '%s', %d, now(), '%s', %d, '%s', %d, %d, %d, %d, %d)",
            $nugoInvoiceNumber,
            $customer->getId(),
            str_replace("'","''",json_encode($content)),
            $this->version,
            Invoices::TYPE_TRIP,
            str_replace("-","",$invoiceDate),
            $tripPayment->getTotalCost(),
            $this->ivaPercentage,
            $fleet->getId(),
            $tripPayment->getPartner()->getId());

        $this->entityManager->getConnection()->executeUpdate($sql);

        $sql = sprintf("SELECT i FROM \SharengoCore\Entity\invoices i WHERE i.invoiceNumber = '%s'",
            $nugoInvoiceNumber);

        $query =$this->entityManager->createQuery($sql);
        $invoice = $query->getOneOrNullResult();

        $sql = sprintf("UPDATE trip_payments SET invoice_id = %d, status = '%s', invoiced_at = now() WHERE id = %d",
            $invoice->getId(),
            $tripPayment::STATUS_INVOICED,
            $tripPayment->getId());

        $this->entityManager->getConnection()->executeUpdate($sql);

        $this->logger->log(sprintf("%s;INF;invoceCreateForTrip;%d;%s;%s\n", 
           date_create()->format('y-m-d H:i:s'),
           $tripPayment->getTripId(),
           $nugoInvoiceNumber,
           $invoiceDate));

        $result = true;

        return $result;
    }

    /**
     * Update invoice partner
     * 
     * @param TripPayments $tripPayment
     * @param string $nugoInvoiceNumber
     * @param string $invoiceDate
     * @return boolean
     */
    private function invoceUpdateForTrip(TripPayments $tripPayment, $nugoInvoiceNumber, $invoiceDate) {
        $result = false;

        if($this->dryRun) {
            return $result;
        }

        $customer = $tripPayment->getCustomer();
        $fleet = $tripPayment->getTrip()->getFleet();

        $content = $this->getInvoiceContent($tripPayment, $nugoInvoiceNumber, $invoiceDate);

        $sql = sprintf("UPDATE invoices SET invoice_number='%s', customer_id=%d, content='%s', version=%d, type='%s', invoice_date=%d, amount=%d, iva=%d, fleet_id=%d, partner_id=%d " .
            "WHERE id = %d",
            $nugoInvoiceNumber,
            $customer->getId(),
            str_replace("'","''",json_encode($content)),
            $this->version,
            Invoices::TYPE_TRIP,
            str_replace("-","",$invoiceDate),
            $tripPayment->getTotalCost(),
            $this->ivaPercentage,
            $fleet->getId(),
            $tripPayment->getPartner()->getId(),
            $tripPayment->getInvoice()->getId());

        $this->entityManager->getConnection()->executeUpdate($sql);

        $sql = sprintf("UPDATE trip_payments SET invoice_id = %d, status = '%s', invoiced_at = now() WHERE id = %d",
            $tripPayment->getInvoice()->getId(),
            $tripPayment::STATUS_INVOICED,
            $tripPayment->getId());

        $this->entityManager->getConnection()->executeUpdate($sql);

        $this->logger->log(sprintf("%s;INF;invoceUpdateForTrip;%d;%s;%s\n", 
           date_create()->format('y-m-d H:i:s'),
           $tripPayment->getTripId(),
           $nugoInvoiceNumber,
           $invoiceDate));

        $result = true;

        return $result;
    }

    /**
     * Format the content array
     * 
     * @param TripPayments $tripPayment
     * @param string $nugoInvoiceNumber
     * @param string $invoiceDate
     * @return string[]
     */
    private function getInvoiceContent(TripPayments $tripPayment, $nugoInvoiceNumber, $invoiceDate) {

        //var_dump($tripPayment->getId());
        $trip = $tripPayment->getTrip();
        $customer = $tripPayment->getCustomer();
        $fleet = $tripPayment->getTrip()->getFleet();

        $bodyContentBody = [];
        array_push($bodyContentBody,
            [
                [$trip->getId()],
                ["Inizio: " . $trip->getTimestampBeginning()->format("d-m-Y H:i:s"),
                    "Fine: " . $trip->getTimestampEnd()->format("d-m-Y H:i:s")],
                ["Da: " . $trip->getAddressBeginning(),
                    "A: " . $trip->getAddressEnd()],
                [$tripPayment->getTripMinutes() . ' (min)'],
                [$trip->getCar()->getPlate()],
                [$this->parseDecimal($tripPayment->getTotalCost()) . ' €']
        ]);

        $body = [
            'greeting_message' => '',
            'contents' => [
                'header' => [
                    'ID',
                    'Data',
                    'Partenza / Arrivo',
                    'Durata',
                    'Targa',
                    'Totale'
                ],
                'body' => $bodyContentBody,
                'body-format' => [
                    'alignment' => [
                        'left',
                        'left',
                        'left',
                        'left',
                        'left',
                        'right'
                    ]
                ]
            ]
        ];

        $iva = $this->ivaFromTotal($tripPayment->getTotalCost());
        $total = $tripPayment->getTotalCost() - $iva;

        $amounts = [
            'iva' => $this->parseDecimal($iva),
            'total' => $this->parseDecimal($total),
            'grand_total' => $this->parseDecimal($tripPayment->getTotalCost()),
            'grand_total_cents' => $tripPayment->getTotalCost()];

        $header = $fleet->getInvoiceHeader() . $this->getInvoiceHeaderNugo($fleet);

        $result = [
            'body' => $body,
            'invoice_date' => intval(str_replace('-','',$invoiceDate)),
            'amounts' => $amounts,
            'iva' => $this->ivaPercentage,
            'customer' => [
                'name' => $customer->getName(),
                'surname' => $customer->getSurname(),
                'email' => $customer->getEmail(),
                'address' => $customer->getAddress(),
                'town' => $customer->getTown(),
                'province' => $customer->getProvince(),
                'country' => $customer->getCountry(),
                'zip_code' => $customer->getZipCode(),
                'cf' => $customer->getTaxCode(),
                'piva' => $customer->getVat()
            ],
            'type' => Invoices::TYPE_TRIP,
            'template_version' => strval($this->version),
            'header' => $header
        ];

        return $result;
    }

    /**
     * Return the Nugo note in the header
     * 
     * @param Fleet $fleet
     * @return string
     */
    private function getInvoiceHeaderNugo(Fleet $fleet) {
        if($fleet->getCode()==="MO") {
            $city = "C.S. Group S.p.A.";
        } else {
            $city = "C.S. ".$fleet->getName()." S.r.l.";
        }
        return sprintf($this::INVOICE_HEADER_NOTE, $city);
    }

    /**
     * Gets the iva (cents of euro) from the total (cents of euro) 
     *
     * @param integer $total
     * @return integer $iva
     */
    private function ivaFromTotal($total)
    {
        $taxRate = $this->ivaPercentage / 100;
        $priceWithoutTax = round($total / ( 1 + $taxRate));
        $iva = (integer) round($priceWithoutTax * $taxRate);

        return $iva;
    }

    /**
     * @param integer
     * @return string
     */
    private function parseDecimal($decimal)
    {
        return number_format((float) $decimal / 100, 2, ',', '');
    }


    public function tryChargeAccountTest(&$curlResponse, &$jsonResponse) {
        $result = false;
        $curlResponse = null;
        $jsonResponse = null;

        try {

            $json = json_encode(
                array(
                    'referenceId' => 321321,
                    'email' => 'user@mail.com',
                    'type' => 'TRIP',
                    'fleetId' => 1,
                    'amount' => 1232,
                    'currency' => 'EUR'
                )
            );

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->params['payments']['uri'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: sharengo_test_key",
                    "Content-Type: application/json",
                    "charset: UTF-8"
                ),
            ));

            $curlResponse = curl_exec($curl);
            $jsonResponse = json_decode($curlResponse, true);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                var_dump("tryChargeAccountTest(),ERR,". $err);
                $curlResponse = $err;
            } else {
                var_dump("tryChargeAccountTest(),INF,". $curlResponse);
                $result = true;
            }
            
        } catch (\Exception $ex) {
            var_dump("tryChargeAccountTest(),ERR,". $ex->getMessage());
            $jsonResponse = null;
        }

        return $result;
    }

    /**
     * Return a string that is a concatenation of all deactivation reason active for a customer
     * 
     * @param Customers $customer
     * @return string
     */
    private function getCustomerDeactivationReason(Customers $customer) {
        $result = array();

        $deactivationReasonsAll = $this->deactivationService->getAllActive($customer);
        foreach($deactivationReasonsAll as $reason) {
             array_push($result, $reason->getReason());
        }

        return $result;
    }

    /**
     * Export the invoice fron partner, create a txt file on data/export/partner/PARTNER_CODED, 
     * then transer on FTP the file for Gamma.
     * 
     * If $date is null, export invoice of yesterday.
     * If $fleetId is null, export the invoice for all fleets.
     * 
     * 
     * @param bool $dryRun
     * @param bool $noFtp
     * @param string $date
     * @param string $fleetId
     * @return boolean
     */
    public function exportRegistries($dryRun, $noFtp, $date, $fleetId) {
        $result = false;
        $invoicesEntries = [];
        $customersEntries = [];

        $this->dryRun = $dryRun;
        $this->noFtp = $noFtp;

        $path = $this->params['exportRegistries']['path'];
//        $this->logger->setOutputEnvironment(Logger::OUTPUT_ON);
//        $this->logger->setOutputType(Logger::TYPE_CONSOLE);

        if(is_null($date)) {
            $date = date_create('yesterday');
        } else {
            $date = date_create($date);
        }
         $this->logger->log(sprintf("%s;INF;exportRegistries;date=%s;fleetId=%s\n", 
            date_create()->format('y-m-d H:i:s'),
            $date->format('y-m-d'),
            $fleetId));

        $this->connectToServer($this->exportConfig);
        $invoices = $this->retriveInvoicesByDateAndFleet($date, $fleetId);

         $this->logger->log(sprintf("%s;INF;exportRegistries;count=%d\n",
            date_create()->format('y-m-d H:i:s'),
            count($invoices)));

        foreach($invoices as $invoice) {
            $this->logger->log(sprintf("%s;INF;exportRegistries;num=%s;date=%d;fleet=%d\n",
                date_create()->format('y-m-d H:i:s'),
                $invoice->getInvoiceNumber(),
                $invoice->getInvoiceDate(),
                $invoice->getFleetId()));

            $fleetName = $invoice->getFleetName();
            if (!array_key_exists($fleetName, $invoicesEntries)) {
                $invoicesEntries[$fleetName] = '';
            }
            $invoicesEntries[$fleetName] .= $this->invoicesService->getExportDataForInvoice($invoice) . "\r\n";

            if (!array_key_exists($fleetName, $customersEntries)) {
                $customersEntries[$fleetName] = '';
            }
            $customersEntries[$fleetName] .= $this->customersService->getExportDataForCustomer($invoice->getCustomer()) . "\r\n";

            // Export invoices data
            $this->exportData($date, $invoicesEntries, self::TYPE_INVOICES, $path);

            // Export customers data
            $this->exportData($date, $customersEntries, self::TYPE_CUSTOMERS, $path);
        }

        return $result;
    }

    private function retriveInvoicesByDateAndFleet(\DateTime $date, $fleetId) {
        $fleet = null;

        if(!is_null($fleetId)) {
            $fleet = $this->fleetService->getFleetById($fleetId);
        }

        return $this->invoicesService->getInvoicesByDateAndFleetJoinCustomers($date, $fleet, $this->partner);
    }

    /**
     * @param \DateTime $date
     * @param string[] $entries
     * @param string $type
     * @param string $path
     */
    private function exportData(\DateTime $date, $entries, $type, $path)
    {
        if (!$this->dryRun && !empty($entries)) {
            foreach ($entries as $fleetName => $entry) {
                $fileName = "export" . $type . '_' . $date->format('Y-m-d') . ".txt";
                $this->ensurePathExistsLocally($path . $fleetName);
                $file = fopen($path . $fleetName . '/' . $fileName, 'w');
                fwrite($file, $entry);
                fclose($file);

                $this->logger->log(sprintf("%s;INF;exportData;type=%s;fileName=%s\n", 
                     date_create()->format('y-m-d H:i:s'),
                     $type,
                     $fileName));

                $this->exportToFtp($path . $fleetName . '/' . $fileName, 'partner/nugo/' . $fleetName . '/' . $fileName);
            }
        }
    }

    /**
     * Checks wether path exists under data/export and creates it if it doesn't
     * @param string $path
     */
    private function ensurePathExistsLocally($path)
    {
        if (!file_exists($path)) {
            if (mkdir($path)) {
                $this->logger->log("Done!\n");
            } else {
                $this->logger->log(sprintf("%s;ERR;ensurePathExistsLocally;%s;FAIL\n", 
                    date_create()->format('y-m-d H:i:s'),
                    $path));
                exit;
            }
        }
    }

    /**
     * Attempts connection to ftp server
     * @param string[] $config
     */
    private function connectToServer($config)
    {
        if (!$this->noFtp) {
            $this->ftpConn = ftp_connect($config['server']);
            if (!$this->ftpConn) {
                $this->logger->log(sprintf("%s;ERR;connectToServer;%s;FAIL\n", 
                    date_create()->format('y-m-d H:i:s'),
                    $config['server']));
                die;
            }
            $login = ftp_login($this->ftpConn, $config['name'], $config['password']);
            ftp_pasv($this->ftpConn, true);

        }
    }

    /**
     * 
     * @param string $from
     * @param string $to
     */
    private function exportToFtp($from, $to)
    {
        if (!$this->noFtp) {
            if (ftp_put($this->ftpConn, $to, $from, FTP_ASCII)) {
                $this->logger->log(sprintf("%s;INF;exportToFtp;%s;%s\n", 
                    date_create()->format('y-m-d H:i:s'),
                    $from,
                    $to));
            } else {
                $this->logger->log(sprintf("%s;ERR;exportToFtp;%s;%s;FAIL\n", 
                    date_create()->format('y-m-d H:i:s'),
                    $from,
                    $to));
            }
        }
    }
}

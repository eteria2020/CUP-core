<?php

namespace SharengoCore\Service\Partner;

use MvLabsDriversLicenseValidation\Service\PortaleAutomobilistaValidationService;

use SharengoCore\Service\SimpleLoggerService as Logger;
use SharengoCore\Service\CustomersService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\FleetService;
use SharengoCore\Service\UserEventsService;
use SharengoCore\Service\DriversLicenseValidationService;
use SharengoCore\Service\CountriesService;
use SharengoCore\Service\InvoicesService;

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

class TelepassService
{
    const PAYMENT_LABEL = 'TPAY';
    const INVOICE_HEADER_NOTE = '<br>Documento emesso da TELEPASS S.p.A. a nome e per conto di %s ';
    const TYPE_INVOICES = "Invoices";
    const TYPE_CUSTOMERS = "Customers";








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
    private $partnerName = 'telepass';

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
     * @var PartnersRepository
     */
    private $partnersRepository;

    /**
     *
     * @var TripsRepository
     */
    private $tripsRepository;

    /**
     *
     * @var CustomersService
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

    /**
     * TelepassService constructor.
     * @param EntityManager $entityManager
     * @param Logger $logger
     * @param $config
     * @param CustomersRepository $customersRepository
     * @param PartnersRepository $partnersRepository
     * @param TripsRepository $tripsRepository
     * @param CustomersService $customersService
     * @param CustomerDeactivationService $deactivationService
     * @param FleetService $fleetService
     * @param ProvincesRepository $provincesRepository
     * @param UserEventsService $userEventsService
     * @param CountriesService $countriesService
     * @param InvoicesService $invoicesService
     * @param DriversLicenseValidationService $driversLicenseValidationService
     * @param PortaleAutomobilistaValidationService $portaleAutomobilistaValidationService
     */
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

                $response = 403;
                $partnerResponse = array(
                    "uri" => $uri,
                    "status" => $response,
                    "statusFromProvider" => $statusFromProvider,
                    "message" => "customer not belong to partner"
                );
            }

        } else {
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

            $key = 'birthCountry';
            $value = $this->getDataFormatedLower($contentArray, $key);
            $counrty = $this->countriesService->getCountryByName($value);

            if (!is_null($counrty)) {
                $contentArray[$key] = $value;

                if($counrty->getCode()=='it') {
                    $key = 'birthProvince';
                    $value = $this->getDataFormatedLower($contentArray, $key);
                    $province = $this->provincesRepository->findOneBy(array('code' => strtoupper($value)));
                    if (!is_null($province)) {
                        $contentArray[$key] = $province->getCode();
                    } else {
                        $strError .= sprintf('Invalid %s ', $key);
                        array_push($errorArray, $key);
                    }

                } else {
                    $contentArray['birthProvince'] = 'EE';
                }

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

                $key2 = 'country';
                $value = $this->getDataFormatedLower($address, $key2);
                $counrty = $this->countriesService->getCountryByName($value);

                if (!is_null($counrty)) {
                    $contentArray['address'][$key2] = $counrty->getCode();

                    if($counrty->getCode()=='it') {
                        $key2 = 'province';
                        $value = $this->getDataFormatedLower($address, $key2);
                        $province = $this->provincesRepository->findOneBy(array('code' => strtoupper($value)));
                        if (!is_null($province)) {
                            $contentArray['address'][$key2] = $province->getCode();
                        } else {
                            $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                            array_push($errorArray, $key.'.'.$key2);
                        }

                        $key2 = 'zip';
                        $value = $this->getDataFormatedLower($address, $key2, FALSE);
                        $validator = new \Application\Form\Validator\ZipCode(array('country' => 'it'));
                        if ($validator->isValid($value)) {
                            $contentArray['address'][$key2] = $value;
                        } else {
                            $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                            array_push($errorArray, $key.'.'.$key2);
                        }

                    } else {
                        $contentArray['address']['province'] = 'EE';

                        $key2 = 'zip';
                        $value = $this->getDataFormatedLower($address, $key2, FALSE);
                        if (strlen($value) > 0) {
                            $contentArray['address'][$key2] = $value;

                        }else {
                            $strError .= sprintf('Invalid %s.%s ', $key, $key2);
                            array_push($errorArray, $key.'.'.$key2);
                        }
                    }

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
                $counrty = $this->countriesService->getCountryByName($value);

                if (!is_null($counrty)) {
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
     * Create a new Driver License Validation
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

            $data = $this->driversLicenseValidationService->fixDataForValidationDriverLicense($data);

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
     * Insert a CustomerDeactivation fake because we consider the driver license from Telepass always valid.
     *
     * @param Customers $customer
     * @param array $drivingLicense
     * @return CustomerDeactivation
     */
    private function newCustomerDeactivations(Customers $customer, $drivingLicense) {

        $details = array('deactivation' => $drivingLicense);
        $customerDeactivations = new CustomerDeactivation($customer, CustomerDeactivation::INVALID_DRIVERS_LICENSE, $details);

        $details = array('reactivation' => $drivingLicense,);
        $customerDeactivations->reactivate($details, date_create(), null);

        $this->entityManager->persist($customerDeactivations);
        $this->entityManager->flush();

        return $customerDeactivations;
    }

    /**
     * Test curl request to perform a charge account
     *
     * @param $curlResponse
     * @param $jsonResponse
     * @return bool
     */
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

            var_dump($this->params['payments']['uri']);
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
                var_dump("tryChargeAccountTest();ERR,". $err);
                $curlResponse = $err;
            } else {
                var_dump("tryChargeAccountTest();INF;". $curlResponse);
                json_decode($curlResponse, true);    // only for test json format
                var_dump("tryChargeAccountTest();INF;". json_last_error() == JSON_ERROR_NONE);
                $result = (json_last_error() == JSON_ERROR_NONE);
            }

        } catch (\Exception $ex) {
            var_dump("tryChargeAccountTest();ERR;EXC;". $ex->getMessage());
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

}


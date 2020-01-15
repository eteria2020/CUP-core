<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Entity\Repository\PartnersRepository;
use SharengoCore\Entity\Partners;

use SharengoCore\Service\FleetService;
use SharengoCore\Service\Partner\TelepassService;
use SharengoCore\Service\Partner\NugoService;
use SharengoCore\Service\CountriesService;
use SharengoCore\Service\DriversLicenseValidationService;

use Doctrine\ORM\EntityManager;

class PartnerService implements ValidatorServiceInterface
{

    /*
     * @var EntityManager
     */
    private $entityManager;

    /*
     * @var CustomersRepository
     */
    private $customersRepository;

    /*
     * @var CountriesService 
     */
    private $countriesService;

    /**
     * @var DriversLicenseValidationService 
     */
    private $driversLicenseValidationService;

    /*
     * @var CustomersRepository
     */
    private $partnersRepository;

    /*
     * @var FleetService
     */
    private $fleetService;

    /*
     * @var TelepassService
     */
    private $telepassService;

    /*
     * @var NugoService
     */
    private $nugoService;

    /**
     * 
     * @param EntityManager $entityManager
     * @param CustomersRepository $customersRepository
     * @param CountriesService $countriesService
     * @param DriversLicenseValidationService $driversLicenseValidationService
     * @param PartnersRepository $partnersRepository
     * @param FleetService $fleetService
     * @param TelepassService $telepassService
     * @param NugoService $nugoService
     */
    public function __construct(
        EntityManager $entityManager,
        CustomersRepository $customersRepository,
        CountriesService $countriesService,
        DriversLicenseValidationService $driversLicenseValidationService,
        PartnersRepository $partnersRepository,
        FleetService $fleetService,
        TelepassService $telepassService,
        NugoService $nugoService
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $customersRepository;
        $this->countriesService = $countriesService;
        $this->driversLicenseValidationService = $driversLicenseValidationService;
        $this->partnersRepository = $partnersRepository;
        $this->fleetService = $fleetService;
        $this->telepassService = $telepassService;
        $this->nugoService = $nugoService;
    }

    public function findByEmail($email)
    {
        return $this->customersRepository->findByCI('email', $email);
    }

    /**
     * Find a partners enabled by Code
     *
     * @param $code
     * @return mixed
     */
    public function findEnabledByCode($code)
    {
        return $this->partnersRepository->findOneBy(array('code' => $code, 'enabled' => true));
    }

    /**
     * Find a partners enabled by Code, and params contain a Json "paymets/fleet_id" match the id's $fleet.
     * Otherway return null.
     *
     * Used in GpWebPay Module for select the correct partner.
     *
     * @param $code
     * @param Fleet $fleet
     * @return |null
     */
    public function findEnabledByCodeByFleet($code, Fleet $fleet)
    {
        $result = null;
        $partners = $this->partnersRepository->findBy(array('code' => $code, 'enabled' => true));

        foreach($partners as $partner) {
            $params = json_decode( $partner->getParams(), true);
            if(isset($params['payments'])) {
                if(isset($params['payments']['fleet_id'])) {
                    if(intval($params['payments']['fleet_id']) == $fleet->getId()) {
                        $result = $partner;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public function partnerData($param){
        return $this->customersRepository->partnerData($param);
    }

    public function getPartnerCode($contentArray, $keyValue) {
        $result ="";

        if (isset($contentArray[$keyValue])) {
            $result = strtolower($contentArray[$keyValue]);
        }

    return $result;
    }

    /**
     * 
     * @param Partners $partner
     * @param array $contentArray
     * @param array $partnerResponse
     * @return array
     */
    public function signup(Partners $partner, $contentArray, &$partnerResponse){
        $result = 403;  // 403 Forbidden
        $partnerResponse ="";

        if($partner->getCode() == $this->telepassService->getPartnerName()) {
            $result = $this->telepassService->signup($partner, $contentArray, $partnerResponse);
        } elseif ($partner->getCode() == $this->nugoService->getPartnerName()) {
            $result = $this->nugoService->signup($partner, $contentArray, $partnerResponse);
        }

        return $result;
    }

    /**
     * Send a notify message to Partner the customer status. 
     * If $customer is null, loop over all customers belog to $partner
     * 
     * Only for customers belogn to Nugo partner
     * 
     * @param Customers $customer
     * @return boolean
     */
    public function notifyCustomerStatus(Partners $partner, Customers $customer = null){
        $result = false;
        $customers = array();

         if(is_null($customer)) { 
             $customers = $this->partnersRepository->findCustomersBelongPartner($partner);
         }
         else {
            if($this->partnersRepository->isBelongCustomerPartner($partner, $customer)) {
                array_push($customers, $customer);
            }
         }

        if($partner->getCode()== $this->nugoService->getPartnerName()) {    // only for Nugo
            foreach($customers as $customer) {
                $result = $this->nugoService->notifyCustomerStatus($customer);
            }
        }
        return $result;
    }

    public function tryChargeAccountTest(&$culrResponse, &$jsonResponse){
        $this->nugoService->tryChargeAccountTest($culrResponse, $jsonResponse);
    }

    /**
     * Import invoices from partner end point.
     * 
     * Only for Nugo
     * 
     * @param boolean $dryRun Flag for debug
     * @param Partners $partner PArmten object
     * @param \DateTime $date Date of istance
     * @param int $fleetId Fleet id
     * @return type
     */
    public function importInvoice($dryRun, Partners $partner, \DateTime $date, $fleetId) {
        $result = null;

        if($partner->getCode() == $this->nugoService->getPartnerName()) {
            $this->nugoService->importInvoice($dryRun, $date, $fleetId);
        }

        return $result;
    }

    /**
     * Deactivate link between partner and customer, and disable the contract width partner
     * 
     * @param Partners $partner
     * @param Customers $customer
     */
    public function deactivatePartnerCustomer(Partners $partner, Customers $customer) {
        if($this->partnersRepository->isBelongCustomerPartner($partner, $customer)) {
            $this->partnersRepository->deactivatePartnerCustomer($partner, $customer);
        }
    }


    /**
     * Check if the reamo address of request is includene inside the list of $listOfValidIp
     * 
     * If $listOfValidIp is null or emptry return true.
     * 
     * @param $listOfValidIp
     * @return bool
     */
    public static function isRemoteAddressValid($listOfValidIp) {
        $result = true;

        try {
            if(is_null($listOfValidIp)) {
                return $result;
            }
            
            $listOfValidIp = trim($listOfValidIp);
            
            if($listOfValidIp=='') {
                return $result;
            }
            
            $remoteAddress = self::getRemoteAddress();
            
            if(strpos($listOfValidIp, $remoteAddress) !== false){
                $result = true;
            } else {
                $result = false;
            }
        } catch (Exception $ex) {
            
        }
        
        return $result;
    }




        /**
     * Return the id from remote address of request or an empty string.
     * From https://gist.github.com/cballou/2201933
     * 
     * @return string
     */
    public static function getRemoteAddress() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    // attempt to validate IP
                    if (self::validate_ip($ip)) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }

    /**
     * Remove the BOM (Byte Order Mark) char from the $text
     *
     * https://it.wikipedia.org/wiki/Byte_Order_Mark
     * 
     * @param $text
     * @return string|string[]|null
     */
    public static function removeUtf8Bom($text)
    {
        $bom = pack('H*','EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     */
    private static function validate_ip($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param boolean $dryRun
     * @param Partners $partner
     * @param string $date
     * @param string $fleetId
     */
    public function exportRegistries($dryRun, $noFtp, Partners $partner, $date, $fleetId) {

        if($partner->getCode() == $this->nugoService->getPartnerName()) {
            $this->nugoService->exportRegistries($dryRun, $noFtp, $date, $fleetId);
        }

    }
}

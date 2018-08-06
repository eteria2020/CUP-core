<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
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

    public function findEnabledByCode($code)
    {
        return $this->partnersRepository->findOneBy(array('code' => $code, 'enabled' => true));
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

    public function notifyCustomerStatusTest(){
        $this->nugoService->notifyCustomerStatusTest();
    }

    public function tryChargeAccountTest(){
        $this->nugoService->tryChargeAccountTest();
    }

    /**
     * Import invoices from partner end point.
     * 
     * Only for Nugo
     * 
     * @param boolean $dryRun Flag for debug
     * @param Partners $partner PArmten object
     * @param Date $date Date of istance
     * @param int $fleetId Fleet id
     * @return type
     */
    public function importInvoice($dryRun, Partners $partner, $date, $fleetId) {
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
}

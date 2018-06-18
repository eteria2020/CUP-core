<?php

namespace SharengoCore\Service;

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

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomersRepository
     */
    private $customersRepository;

    /**
     *
     * @var CountriesService 
     */
    private $countriesService;

    /**
     *
     * @var DriversLicenseValidationService 
     */
    private $driversLicenseValidationService;

    /**
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

    public function findEnabledBycode($code)
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
        $result = 404;
        $partnerResponse ="";

        if($partner->getCode() == $this->telepassService->getPartnerName()) {
            $result = $this->telepassService->signup($partner, $contentArray, $partnerResponse);
        } elseif ($partner->getCode() == $this->nugoService->getPartnerName()) {
            $result = $this->nugoService->signup($partner, $contentArray, $partnerResponse);
        }

        return $result;
    }


    public function getDataForDriverLicenseValidation(Partners $partner, Customers $customer){
        $result = null;

        if ($partner->getCode() == $this->nugoService->getPartnerName()) {
            $result = [
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

            $result['birthCountryMCTC'] = $this->countriesService->getMctcCode($result['birthCountry']);
            $result['birthProvince'] = $this->driversLicenseValidationService->changeProvinceForValidationDriverLicense($result);
        }

        return $result;
    }
}

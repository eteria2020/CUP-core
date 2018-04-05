<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Entity\Repository\PartnersRepository;
use SharengoCore\Service\FleetService;
use SharengoCore\Service\TelepassService;
use SharengoCore\Entity\Partners;

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

    public function __construct(
        EntityManager $entityManager,
        CustomersRepository $customersRepository,
        PartnersRepository $partnersRepository,
        FleetService $fleetService,
        TelepassService $telepassService
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $customersRepository;
        $this->partnersRepository = $partnersRepository;
        $this->fleetService = $fleetService;
        $this->telepassService = $telepassService;
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
        }

        return $result;
    }

}

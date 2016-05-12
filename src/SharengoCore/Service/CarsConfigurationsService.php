<?php

namespace SharengoCore\Service;

use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\CarsConfigurations;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Repository\CarsConfigurationsRepository;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Entity\Repository\FleetRepository;
use SharengoCore\Service\DatatableService;
use Zend\Authentication\AuthenticationService as UserService;

class CarsConfigurationsService
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  CarsConfigurationsRepository */
    private $carsConfigurationsRepository;

    /** @var  CarsRepository */
    private $carsRepository;

    /** @var  FleetsRepository */
    private $fleetsRepository;

    /** @var DatatableService */
    private $datatableService;

    /** @var UserService   */
    private $userService;

    /**
     * @param EntityManager                 $entityManager
     * @param CarsConfigurationsRepository  $carsConfigurationsRepository
     * @param CarsRepository                $carsRepository
     * @param FleetsRepository              $fleetsRepository
     * @param DatatableService              $datatableService
     * @param UserService                   $userService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsConfigurationsRepository $carsConfigurationsRepository,
        CarsRepository $carsRepository,
        FleetRepository $fleetsRepository,
        DatatableService $datatableService,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->carsConfigurationsRepository = $carsConfigurationsRepository;
        $this->carsRepository = $carsRepository;
        $this->fleetsRepository = $fleetsRepository;
        $this->datatableService = $datatableService;
        $this->userService = $userService;
    }
    
    /**
     * @return mixed
     */
    public function getListCarsConfigurations()
    {
        return $this->carsConfigurationsRepository->findAll();
    }


    public function getCar($carPlate)
    {
        return $this->carsRepository->find($carPlate);
    }

    public function getCars()
    {
        return $this->carsRepository->findAll;
    }

    public function getFleet($fleetId)
    {
        return $this->fleetsRepository->find($fleetId);
    }

    public function getFleets()
    {
        return $this->fleetsRepository->findAll();
    }

    public function getTotalCarsConfigurations()
    {
        return $this->carsConfigurationsRepository->getTotalCarsConfigurations();
    }

    public function getListCarsConfigurationsFiltered($filters = [])
    {
        return $this->carsConfigurationsRepository->findBy($filters, ['id' => 'ASC']);
    }

    public function getCarConfigurationById($id)
    {
        return $this->carsConfigurationsRepository->find($id);
    }

    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $carsConfigurations = $this->datatableService->getData('CarsConfigurations', $as_filters, $count);

        if ($count) {
            return $carsConfigurations;
        }

        return array_map(function (CarsConfigurations $carsConfigurations) {
            return [
                'e' => [
                    'model' => $carsConfigurations->getModel(),
                    'key' => $carsConfigurations->getKey(),
                    'value' => $carsConfigurations->getValue(),
                ],
                'c' => [
                    'plate' => $carsConfigurations->getCarPlate(),
                ],
                'f' => [
                    'name' => $carsConfigurations->getFleetName(),
                ],
                'button' => $carsConfigurations->getId(),
            ];
        }, $carsConfigurations);
    }

    public function save(CarsConfigurations $configuration, $value) {
        if(!empty($value)) {
            $configuration->setValue($value);
        }

        $this->entityManager->persist($configuration);
        $this->entityManager->flush();

        return $configuration;
    }
}

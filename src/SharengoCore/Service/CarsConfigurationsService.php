<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\CarsConfigurations;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Repository\CarsConfigurationsRepository;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Entity\Repository\FleetRepository;
use SharengoCore\Service\DatatableServiceInterface;
// Externals
use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService as UserService;

class CarsConfigurationsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CarsConfigurationsRepository
     */
    private $carsConfigurationsRepository;

    /**
     * @var CarsRepository
     */
    private $carsRepository;

    /**
     * @var FleetsRepository
     */
    private $fleetsRepository;

    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param EntityManager $entityManager
     * @param CarsConfigurationsRepository $carsConfigurationsRepository
     * @param CarsRepository $carsRepository
     * @param FleetsRepository $fleetsRepository
     * @param DatatableServiceInterface $datatableService
     * @param UserService $userService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsConfigurationsRepository $carsConfigurationsRepository,
        CarsRepository $carsRepository,
        FleetRepository $fleetsRepository,
        DatatableServiceInterface $datatableService,
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
     * This method return an array containing all the CarsConfigurations
     *
     * @return mixed
     */
    public function getListCarsConfigurations()
    {
        return $this->carsConfigurationsRepository->findAll();
    }

    /**
     * This method return an instance of Car, find by plate.
     *
     * @param string $carPlate
     * @return Cars
     */
    public function getCar($carPlate)
    {
        return $this->carsRepository->find($carPlate);
    }

    /**
     * This method return an array containing all the Cars.
     *
     * @return mixed
     */
    public function getCars()
    {
        return $this->carsRepository->findAll;
    }

    /**
     * This method return an instance of Fleet, find by id.
     *
     * @param int $fleetId
     * @return Fleet
     */
    public function getFleet($fleetId)
    {
        return $this->fleetsRepository->find($fleetId);
    }

    /**
     * This method return an array containing all the Fleet.
     *
     * @return mixed
     */
    public function getFleets()
    {
        return $this->fleetsRepository->findAll();
    }

    /**
     * This method return the number of CarsConfigurations.
     *
     * @return int
     */
    public function getTotalCarsConfigurations()
    {
        return $this->carsConfigurationsRepository->getTotalCarsConfigurations();
    }

    public function getListCarsConfigurationsFiltered($filters = [])
    {
        return $this->carsConfigurationsRepository->findBy($filters, ['id' => 'ASC']);
    }

    /**
     * This method return an instance of CarsConfigurations, find by id.
     *
     * @param int $carConfigurationId
     * @return CarsConfigurations
     */
    public function getCarConfigurationById($carConfigurationId)
    {
        return $this->carsConfigurationsRepository->find($carConfigurationId);
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

    /**
     * This method save an instance of CarsConfigurations, and "its value".
     *
     * @param CarsConfigurations $carConfiguration
     * @param string $value
     * @return CarsConfigurations
     */
    public function save(CarsConfigurations $carConfiguration, $value) {
        if(!empty($value)) {
            $carConfiguration->setValue($value);
        }

        $this->entityManager->persist($carConfiguration);
        $this->entityManager->flush();

        return $carConfiguration;
    }

    /**
     * This method delete an instance of CarsConfigurations.
     *
     * @param CarsConfigurations $carConfiguration
     */
    public function deleteCarConfiguration(CarsConfigurations $carConfiguration)
    {
        $this->entityManager->remove($carConfiguration);
        $this->entityManager->flush();
    }
}

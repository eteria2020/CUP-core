<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\CarsConfigurations;
use SharengoCore\Entity\Repository\CarsConfigurationsRepository;
use SharengoCore\Service\DatatableServiceInterface;
// Externals
use Doctrine\ORM\EntityManager;

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
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @param EntityManager $entityManager
     * @param CarsConfigurationsRepository $carsConfigurationsRepository
     * @param DatatableServiceInterface $datatableService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsConfigurationsRepository $carsConfigurationsRepository,
        DatatableServiceInterface $datatableService
    ) {
        $this->entityManager = $entityManager;
        $this->carsConfigurationsRepository = $carsConfigurationsRepository;
        $this->datatableService = $datatableService;
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

    public function getDataDataTable(array $filters = [], $count = false)
    {
        $carsConfigurations = $this->datatableService->getData('CarsConfigurations', $filters, $count);

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
    public function save(CarsConfigurations $carConfiguration, $value)
    {
        if (!empty($value)) {
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

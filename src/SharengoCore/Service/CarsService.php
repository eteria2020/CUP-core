<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Service\DatatableService;

use Zend\Authentication\AuthenticationService as UserService;

class CarsService
{
    /** @var  CarsRepository */
    private $carsRepository;

    /**
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @param CarsRepository   $carsRepository
     * @param DatatableService $datatableService
     */
    public function __construct(CarsRepository $carsRepository, DatatableService $datatableService)
    {
        $this->carsRepository = $carsRepository;

        $this->datatableService = $datatableService;
    }

    /**
     * @return mixed
     */
    public function getListCars()
    {
        return $this->carsRepository->findAll();
    }

    public function getTotalCars()
    {
        return $this->carsRepository->getTotalCars();
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $cars = $this->datatableService->getData('Cars', $as_filters);

        return array_map(function (Cars $cars) {
            return [
                'plate'               => $cars->getPlate(),
                'manufactures'        => $cars->getManufactures()
            ];
        }, $cars);
    }
}

<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Service\DatatableService;

use Zend\Authentication\AuthenticationService as UserService;

class CarsService
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  CarsRepository */
    private $carsRepository;

    /** @var DatatableService */
    private $datatableService;

    /**
     * @param EntityManager    $entityManager
     * @param CarsRepository   $carsRepository
     * @param DatatableService $datatableService
     */
    public function __construct(EntityManager $entityManager, CarsRepository $carsRepository, DatatableService $datatableService)
    {
        $this->entityManager = $entityManager;
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

    public function getCarByPlate($plate) {

        return $this->carsRepository->find($plate);
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $cars = $this->datatableService->getData('Cars', $as_filters);

        return array_map(function (Cars $cars) {
            return [
                'plate'        => $cars->getPlate(),
                'manufactures' => $cars->getManufactures(),
                'model'        => $cars->getModel(),
                'button'       => $cars->getPlate(),

            ];
        }, $cars);
    }

    public function saveData(Cars $cars, $defaultData = true)
    {
        if($defaultData) {
            $cars->setStatus('operative');
            $cars->setIntCleanliness('clean');
            $cars->setExtCleanliness('clean');
            $cars->setNumber(0);
            $cars->setKm(0);
            $cars->setLatitude(0);
            $cars->setLongitude(0);
            $cars->setBattery(0);
            $cars->setLocation('POINT(0 0)');
            $cars->setMac('');
            $cars->setImei('');
            $cars->setRpm(0);
            $cars->setSpeed(0);
            $cars->setFirmwareVersion('');
            $cars->setSoftwareVersion('');
            $cars->setObcInUse(0);
            $cars->setObcWlSize(0);
        }

        $this->entityManager->persist($cars);
        $this->entityManager->flush();
        return $cars;
    }

    public function deleteCar(Cars $car)
    {
        $this->entityManager->remove($car);
        $this->entityManager->flush();
    }
}

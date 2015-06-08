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
    public function __construct(
        EntityManager $entityManager,
        CarsRepository $carsRepository,
        DatatableService $datatableService
    ) {
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

    public function getCarByPlate($plate)
    {

        return $this->carsRepository->find($plate);
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $cars = $this->datatableService->getData('Cars', $as_filters);

        return array_map(function (Cars $cars) {

            $clean = sprintf('Interna: %s Esterna: %s', $cars->getIntCleanliness(), $cars->getExtCleanliness());

            return [
                'plate'        => $cars->getPlate(),
                'manufactures' => $cars->getManufactures(),
                'model'        => $cars->getModel(),
                'clean'        => $clean,
                'position'     => sprintf('Lon: %s Lat: %s', $cars->getLongitude(), $cars->getLatitude()),
                'lastContact'  => is_object($cars->getLastContact()) ? $cars->getLastContact()->format('d-m-Y H:i:s') : '',
                'rpm'          => $cars->getRpm(),
                'speed'        => $cars->getSpeed(),
                'km'           => $cars->getKm(),
                'running'      => $cars->getRunning() ? 'Si' : 'No',
                'parking'      => $cars->getParking() ? 'Si' : 'No',
                'button'       => $cars->getPlate(),
            ];
        }, $cars);
    }

    public function saveData(Cars $cars, $defaultData = true)
    {
        if ($defaultData) {
            $cars->setIntCleanliness('clean');
            $cars->setExtCleanliness('clean');
            $cars->setStatus('operative');
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

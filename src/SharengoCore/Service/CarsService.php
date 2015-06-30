<?php

namespace SharengoCore\Service;

use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Entity\Repository\UpdateCarsRepository;
use SharengoCore\Entity\UpdateCars;
use SharengoCore\Service\DatatableService;

use SharengoCore\Utility\StatusCar;
use Zend\Authentication\AuthenticationService as UserService;

class CarsService
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  CarsRepository */
    private $carsRepository;

    /** @var  UpdateCarsRepository */
    private $updateCarsRepository;

    /** @var DatatableService */
    private $datatableService;

    /** @var UserService   */
    private $userService;

    /**
     * @param EntityManager    $entityManager
     * @param CarsRepository   $carsRepository
     * @param DatatableService $datatableService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsRepository $carsRepository,
        UpdateCarsRepository $updateCarsRepository,
        DatatableService $datatableService,
        UserService $userService
    ) {
        $this->entityManager = $entityManager;
        $this->carsRepository = $carsRepository;
        $this->updateCarsRepository = $updateCarsRepository;
        $this->datatableService = $datatableService;
        $this->userService = $userService;
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

            $positionLink = sprintf('<a href="http://maps.google.com/?q=%s,%s" target="_blank">Mappa</a>',
                $cars->getLatitude(), $cars->getLongitude());

            return [
                'e'            => [
                    'plate'       => $cars->getPlate(),
                    'label'       => $cars->getLabel(),
                    'battery'     => $cars->getBattery(),
                    'lastContact' => is_object($cars->getLastContact()) ? $cars->getLastContact()->format('d-m-Y H:i:s') : '',
                    'km'          => $cars->getKm(),
                    'status'      => $cars->getStatus(),

                ],
                'clean'        => $clean,
                'position'     => sprintf('Lat: %s Lon: %s ', $cars->getLatitude(), $cars->getLongitude()),
                'positionLink' => $positionLink,
                'button'       => $cars->getPlate(),
            ];
        }, $cars);
    }

    public function saveData(Cars $cars, $defaultData = true)
    {
        $cars->setPlate(strtoupper($cars->getPlate()));

        if ($defaultData) {
            $cars->setIntCleanliness('clean');
            $cars->setExtCleanliness('clean');
            $cars->setStatus('operative');
        }

        $this->entityManager->persist($cars);
        $this->entityManager->flush();
        return $cars;
    }

    public function updateCar(Cars $cars, $lastStatus, $postData)
    {
        $location = !empty($postData['location']) ? $postData['location'] : null;

        if($cars->getStatus() == StatusCar::MAINTENANCE &&
            ($lastStatus == StatusCar::OPERATIVE || $lastStatus == StatusCar::OUT_OF_ORDER) &&
            !is_null($location)) {
            $updateCar = new UpdateCars();
            $updateCar->setCarPlate($cars);
            $updateCar->setLocation($location);
            $updateCar->setNote($postData['note']);
            $updateCar->setUpdate(new \DateTime());
            $updateCar->setWebuser($this->userService->getIdentity());
            $updateCar->setStatus($cars->getStatus());
            $this->entityManager->persist($updateCar);
            $this->entityManager->flush();
        }
    }

    public function deleteCar(Cars $car)
    {
        $this->entityManager->remove($car);
        $this->entityManager->flush();
    }

    public function getStatusCarAvailable($status)
    {
        $as_status = [];

        switch ($status) {

            case StatusCar::OPERATIVE:
            case StatusCar::MAINTENANCE:
                $as_status = [
                    StatusCar::OPERATIVE   => StatusCar::OPERATIVE,
                    StatusCar::MAINTENANCE => StatusCar::MAINTENANCE
                ];
                break;

            case StatusCar::OUT_OF_ORDER:
                $as_status = [
                    StatusCar::OUT_OF_ORDER => StatusCar::OUT_OF_ORDER,
                    StatusCar::MAINTENANCE  => StatusCar::MAINTENANCE
                ];
                break;
        }

        return $as_status;
    }

    public function getLastUpdateCar($plate)
    {
        return $this->updateCarsRepository->findLastUpdateByPlate($plate);
    }
}

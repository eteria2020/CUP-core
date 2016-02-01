<?php

namespace SharengoCore\Service;

use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\CarsMaintenance;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Entity\Repository\CarsDamagesRepository;
use SharengoCore\Entity\Repository\FleetRepository;
use SharengoCore\Entity\Repository\CarsMaintenanceRepository;
use SharengoCore\Service\DatatableService;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Utility\CarStatus;
use Zend\Authentication\AuthenticationService as UserService;

class CarsService
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  CarsRepository */
    private $carsRepository;

    /** @var  CarsMaintenance */
    private $carsMaintenanceRepository;

    /** @var  FleetsRepository */
    private $fleetsRepository;

    /** @var DatatableService */
    private $datatableService;

    /** @var UserService   */
    private $userService;

    /** @var ReservationsService   */
    private $reservationsService;

    /**
     * @param EntityManager    $entityManager
     * @param CarsRepository   $carsRepository
     * @param CarsMaintenance  $carsMaintenanceRepository
     * @param FleetsRepository $fleetsRepository
     * @param DatatableService $datatableService
     * @param UserService      $userService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsRepository $carsRepository,
        CarsMaintenanceRepository $carsMaintenanceRepository,
        CarsDamagesRepository $carsDamagesRepository,
        FleetRepository $fleetsRepository,
        DatatableService $datatableService,
        UserService $userService,
        ReservationsService $reservationsService
    ) {
        $this->entityManager = $entityManager;
        $this->carsRepository = $carsRepository;
        $this->carsMaintenanceRepository = $carsMaintenanceRepository;
        $this->carsDamagesRepository = $carsDamagesRepository;
        $this->fleetsRepository = $fleetsRepository;
        $this->datatableService = $datatableService;
        $this->userService = $userService;
        $this->reservationsService = $reservationsService;
    }


    /**
     * @return mixed
     */
    public function getListCars()
    {
        return $this->carsRepository->findAll();
    }

    public function getFleets()
    {
        return $this->fleetsRepository->findAll();
    }

    public function getFleet($fleetId)
    {
        return $this->fleetsRepository->find($fleetId);
    }

    public function getTotalCars()
    {
        return $this->carsRepository->getTotalCars();
    }

    public function getListCarsFiltered($filters = [])
    {
        return $this->carsRepository->findBy($filters, ['plate' => 'ASC']);
    }

    public function getCarsEligibleForAlarmCheck()
    {
        return $this->carsRepository->findCarsEligibleForAlarmCheck();
    }

    public function getPublicCars()
    {
        return $this->carsRepository->findPublicCars();
    }

    public function getCarByPlate($plate)
    {

        return $this->carsRepository->find($plate);
    }

    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $cars = $this->datatableService->getData('Cars', $as_filters, $count);

        if ($count) {
            return $cars;
        }

        return array_map(function (Cars $cars) {

            $clean = sprintf('Interna: %s<br />Esterna: %s', $cars->getIntCleanliness(), $cars->getExtCleanliness());

            $positionLink = sprintf(
                '<a href="http://maps.google.com/?q=%s,%s" target="_blank">Mappa</a>',
                $cars->getLatitude(),
                $cars->getLongitude()
            );

            return [
                'e' => [
                    'plate' => $cars->getPlate(),
                    'label' => $cars->getLabel(),
                    'battery' => $cars->getBattery(),
                    'lastContact' => is_object($cars->getLastContact()) ? $cars->getLastContact()->format('d-m-Y H:i:s') : '',
                    'km' => $cars->getKm(),
                    'status' => $cars->getStatus(),
                ],
                'f' => [
                    'name' => $cars->getFleet()->getName(),
                ],
                'ci' => [
                    'gps' => $cars->getCarsInfoGps(),
                    'firmwareVersion' => $cars->getCarsInfoFirmwareVersion(),
                    'softwareVersion' => $cars->getCarsInfoSoftwareVersion(),
                ],
                'clean' => $clean,
                'position' => sprintf('Lat: %s<br />Lon: %s ', $cars->getLatitude(), $cars->getLongitude()),
                'positionLink' => $positionLink,
                'button' => $cars->getPlate(),
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

    public function updateCar(Cars $car, $lastStatus, $postData)
    {
        $location = !empty($postData['location']) ? $postData['location'] : null;

        if ($car->getStatus() == CarStatus::MAINTENANCE &&
            !is_null($location)) {
            $carsMaintenance = new CarsMaintenance();
            $carsMaintenance->setCarPlate($car);
            $carsMaintenance->setLocation($location);
            $carsMaintenance->setNotes($postData['note']);
            $carsMaintenance->setUpdateTs(new \DateTime());
            $carsMaintenance->setWebuser($this->userService->getIdentity());
            $this->entityManager->persist($carsMaintenance);
        }

        /* set system reservation according to status change */
        if ($lastStatus != $car->getStatus()) {
            $maintenanceReservation = $this->reservationsService->getMaintenanceReservation($car->getPlate());

            switch ($lastStatus) {
                case CarStatus::OUT_OF_ORDER:
                    if ($car->getStatus() == CarStatus::OPERATIVE) {
                        if (null != $maintenanceReservation) {
                            $maintenanceReservation->setActive(false);
                            $maintenanceReservation->setTosend(true);
                        }
                    } else if ($car->getStatus() == CarStatus::MAINTENANCE) {
                        if (null != $maintenanceReservation) {
                            $maintenanceReservation->setActive(true);
                            $maintenanceReservation->setTosend(true);
                        }
                    }
                    break;
                case CarStatus::OPERATIVE:
                    if ($car->getStatus() == CarStatus::MAINTENANCE) {
                        if (null != $maintenanceReservation) {
                            $maintenanceReservation->setActive(true);
                            $maintenanceReservation->setTosend(true);
                        } else {
                            $this->reservationsService->createMaintenanceReservation($car);
                        }
                    }
                    break;
                case CarStatus::MAINTENANCE:
                    if ($car->getStatus() == CarStatus::OPERATIVE) {
                        if (null != $maintenanceReservation) {
                            $maintenanceReservation->setActive(false);
                            $maintenanceReservation->setTosend(true);
                        }
                    }
                    break;
            }

            if (null != $maintenanceReservation) {
                $this->entityManager->persist($maintenanceReservation);
            }

        }

        $this->entityManager->flush();

    }

    public function updateDamages(Cars $car, array $damages = null)
    {
        $car->setDamages($damages);
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        return $car;
    }

    public function deleteCar(Cars $car)
    {
        $this->entityManager->remove($car);
        $this->entityManager->flush();
    }

    public function getStatusCarAvailable($status)
    {

        switch ($status) {
            case CarStatus::OPERATIVE:
                return [
                    CarStatus::OPERATIVE => CarStatus::OPERATIVE,
                    CarStatus::MAINTENANCE => CarStatus::MAINTENANCE,
                ];

            case CarStatus::MAINTENANCE:
                return [
                    CarStatus::MAINTENANCE => CarStatus::MAINTENANCE,
                    CarStatus::OPERATIVE   => CarStatus::OPERATIVE,
                ];

            case CarStatus::OUT_OF_ORDER:
                return [
                    CarStatus::OUT_OF_ORDER => CarStatus::OUT_OF_ORDER,
                    CarStatus::MAINTENANCE  => CarStatus::MAINTENANCE
                ];
        }

        return [];

    }

    public function getLastCarsMaintenance($plate)
    {
        return $this->carsMaintenanceRepository->findLastCarsMaintenance($plate);
    }

    /**
     * @param Cars $car
     * @return boolean
     */
    public function isCarOutOfBounds(Cars $car)
    {
        return !$this->carsRepository->checkCarInFleetZones($car);
    }

    public function getDamagesList()
    {
        return $this->carsDamagesRepository->findAll();
    }

    /**
     * Returns array of plates of cars that have an active reservation
     * @return string[]
     */
    public function getReservedPlates()
    {
        return $this->carsRepository->findReservedPlates()[0]['value'];
    }

    /**
     * Returns array of plates of cars that have an active trip
     * @return string[]
     */
    public function getBusyPlates()
    {
        return $this->carsRepository->findBusyPlates()[0]['value'];
    }

    /**
     * Returns an array of key => value pairs where the key is the plate of the
     * car and the value is the amount of minutes since the last trip it has made.
     *
     * @return [string => integer]
     */
    public function getMinutesSinceLastTrip()
    {
        return $this->carsRepository->findMinutesSinceLastTrip()[0]['value'];
    }

    /**
     * Returns an array of plates of cars that are out of permitted Zones
     * @return string[]
     */
    public function getOutOfBoundsPlates()
    {
        return $this->carsRepository->findOutOfBoundsPlates()[0]['value'];
    }
}

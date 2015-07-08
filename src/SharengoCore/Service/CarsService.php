<?php

namespace SharengoCore\Service;

use BjyAuthorize\Service\Authorize;
use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\CarsMaintenance;
use SharengoCore\Entity\Repository\CarsRepository;
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
     * @param DatatableService $datatableService
     * @param UserService      $userService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsRepository $carsRepository,
        CarsMaintenanceRepository $carsMaintenanceRepository,
        DatatableService $datatableService,
        UserService $userService,
        ReservationsService $reservationsService
    ) {
        $this->entityManager = $entityManager;
        $this->carsRepository = $carsRepository;
        $this->carsMaintenanceRepository = $carsMaintenanceRepository;
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

    public function updateCar(Cars $car, $lastStatus, $postData)
    {
        $location = !empty($postData['location']) ? $postData['location'] : null;

        if($car->getStatus() == CarStatus::MAINTENANCE &&
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
            switch ($lastStatus) {
                case CarStatus::OUT_OF_ORDER:
                    if ($car->getStatus() == CarStatus::OPERATIVE) {
                        $reservation = $this->reservationsService->getMaintenanceReservation($car->getPlate());
                        if (null != $reservation) {
                            $reservation->setActive(false);
                            $reservation->setTosend(true);
                            $this->entityManager->persist($reservation);
                        }
                    } else if ($car->getStatus() == CarStatus::MAINTENANCE) {
                        $reservation = $this->reservationsService->getMaintenanceReservation($car->getPlate());
                        if (null != $reservation) {
                            $reservation->setActive(true);
                            $reservation->setTosend(true);
                            $this->entityManager->persist($reservation);
                        }
                    }
                    break;
                case CarStatus::OPERATIVE:
                    if ($car->getStatus() == CarStatus::MAINTENANCE) {
                        $this->reservationsService->createMaintenanceReservation($car);
                    }
                    break;
                case CarStatus::MAINTENANCE:
                    if ($car->getStatus() == CarStatus::OPERATIVE) {
                        $reservation = $this->reservationsService->getMaintenanceReservation($car->getPlate());

                        if (null != $reservation) {
                            $reservation->setActive(false);
                            $reservation->setTosend(true);
                            $this->entityManager->persist($reservation);
                        }
                    }
                    break;
            }
        }

        $this->entityManager->flush();
        
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
                    CarStatus::OPERATIVE => CarStatus::OPERATIVE,
                    CarStatus::MAINTENANCE  => CarStatus::MAINTENANCE
                ];
        }

        return [];

    }

    public function getLastCarsMaintenance($plate)
    {
        return $this->carsMaintenanceRepository->findLastCarsMaintenance($plate);
    }
}

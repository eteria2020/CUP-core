<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Cars;
use SharengoCore\Entity\CarsMaintenance;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Repository\CarsRepository;
use SharengoCore\Entity\Repository\CarsDamagesRepository;
use SharengoCore\Entity\Repository\CarsMaintenanceRepository;
use SharengoCore\Entity\Repository\FleetRepository;
use SharengoCore\Entity\Webuser;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Utility\CarStatus;
use SharengoCore\Service\MaintenanceLocationsService;
use SharengoCore\Service\MaintenanceMotivationsService;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\I18n\Translator;

class CarsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CarsRepository
     */
    private $carsRepository;

    /**
     * @var CarsMaintenanceRepository
     */
    private $carsMaintenanceRepository;

    /**
     * @var FleetsRepository
     */
    private $fleetsRepository;

    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @var ReservationsService
     */
    private $reservationsService;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var MaintenanceMotivationsService
     */

    private $maintenanceMotivationsService;
    
    /**
     * @var MaintenanceLocationsService
     */
    private $maintenanceLocationsService;


    /**
     * @param EntityManager $entityManager
     * @param CarsRepository $carsRepository
     * @param CarsMaintenance $carsMaintenanceRepository
     * @param FleetsRepository $fleetsRepository
     * @param DatatableServiceInterface $datatableService
     * @param Translator $translator
     * @param MaintenanceMotivationsService $maintenanceMotivationsService
     * @param MaintenanceLocationsService $maintenanceLocationsService
     */
    public function __construct(
        EntityManager $entityManager,
        CarsRepository $carsRepository,
        CarsMaintenanceRepository $carsMaintenanceRepository,
        CarsDamagesRepository $carsDamagesRepository,
        FleetRepository $fleetsRepository,
        DatatableServiceInterface $datatableService,
        ReservationsService $reservationsService,
        Translator $translator,
        MaintenanceMotivationsService $maintenanceMotivationsService,
        MaintenanceLocationsService $maintenanceLocationsService
    ) {
        $this->entityManager = $entityManager;
        $this->carsRepository = $carsRepository;
        $this->carsMaintenanceRepository = $carsMaintenanceRepository;
        $this->carsDamagesRepository = $carsDamagesRepository;
        $this->fleetsRepository = $fleetsRepository;
        $this->datatableService = $datatableService;
        $this->reservationsService = $reservationsService;
        $this->translator = $translator;
        $this->maintenanceMotivationsService = $maintenanceMotivationsService;
        $this->maintenanceLocationsService = $maintenanceLocationsService;
    }

    /**
     * @return Cars[]
     */
    public function getListCars()
    {
        return $this->carsRepository->findAll();
    }

    /**
     * @return Fleets[]
     */
    public function getFleets()
    {
        return $this->fleetsRepository->findAll();
    }

    /**
     * @param integer $fleetId
     * @return Fleet
     */
    public function getFleet($fleetId)
    {
        return $this->fleetsRepository->find($fleetId);
    }

    /**
     * @return integer
     */
    public function getTotalCars()
    {
        return $this->carsRepository->getTotalCars();
    }

    /**
     * @param array|null $filters
     * @return Cars[]
     */
    public function getListCarsFiltered($filters = [])
    {
        return $this->carsRepository->findBy($filters, ['plate' => 'ASC']);
    }

    /**
     * @return Cars[]
     */
    public function getCarsEligibleForAlarmCheck()
    {
        return $this->carsRepository->findCarsEligibleForAlarmCheck();
    }

    /**
     * @return Cars[]
     */
    public function getPublicCars()
    {
        return $this->carsRepository->findPublicCars();
    }

    public function getPublicFreeCarsByFleet(Fleet $fleet)
    {
        return array_map(function (Cars $cars) {
            return [
                'plate' => $cars->getPlate(),
                'label' => $cars->getLabel(),
                'battery' => $cars->getBattery(),
                'km' => $cars->getKm(),
                'status' => $cars->getStatus(),
                'intCleanliness' => $cars->getIntCleanliness(),
                'extCleanliness' => $cars->getExtCleanliness(),
                'latitude' => $cars->getLatitude(),
                'longitude' => $cars->getLongitude()
            ];
        }, $this->carsRepository->findPublicFreeCarsByFleet($fleet));
    }

    /**
     * @param string $plate
     * @return Cars
     */
    public function getCarByPlate($plate)
    {
        return $this->carsRepository->find($plate);
    }

    /**
     * @param array|null $as_filters
     * @param boolean $count
     * @return mixed|integer
     */
    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $cars = $this->datatableService->getData('Cars', $as_filters, $count);

        if ($count) {
            return $cars;
        }

        return array_map(function (Cars $cars) {

            $clean = sprintf($this->translator->translate("Interna").': %s<br />' . $this->translator->translate("Esterna") . ': %s', $cars->getIntCleanliness(), $cars->getExtCleanliness());

            $positionLink = $this->positionLInk($cars);

            return [
                'e' => [
                    'plate' => $cars->getPlate(),
                    'label' => $cars->getLabel(),
                    'battery' => $cars->getBattery(),
                    'lastContact' => is_object($cars->getLastContact()) ? $cars->getLastContact()->format('d-m-Y H:i:s') : '',
                    'km' => $cars->getKm(),
                    'status' => $cars->getStatus(),
                    'hidden' => $cars->getHidden(),
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

    /**
     * @param Cars $cars
     * @param boolean|null $defaultData
     * @return Cars[]
     */
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

    /**
     * @param Cars $car
     * @param string $lastStatus
     * @param mixed[] $postData
     * @param Webuser $webuser
     */
    public function updateCar(Cars $car, $lastStatus, $postData, Webuser $webuser, $param = false)
    {
        $location = !empty($postData['location']) ? $postData['location'] : null;

        if ($car->getStatus() == CarStatus::MAINTENANCE &&
            !is_null($location)) {
            $carsMaintenance = new CarsMaintenance();
            $carsMaintenance->setCarPlate($car);
            $location = $this->maintenanceLocationsService->getById($postData["location"])[0]; 
            $carsMaintenance->setLocationId($location);
            $carsMaintenance->setLocation($location->getlocation());
            $carsMaintenance->setNotes($postData['note']);
            $carsMaintenance->setUpdateTs(new \DateTime());
            $carsMaintenance->setWebuser($webuser);
            $motivation = $this->maintenanceMotivationsService->getById($postData["motivation"])[0];
            $carsMaintenance->setMotivation($motivation);
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

                            // Update CarsMaintenance endTs if necessary
                            $maintenance = $this->getLastCarsMaintenance($car->getPlate());
                            if ($maintenance instanceof CarsMaintenance && !$maintenance->isEnded()) {
                                if($param){
                                    $maintenance->setNotes($maintenance->getNotes() . ' || ' . $postData['note']);
                                }
                                $maintenance->setEndWebuser($webuser);
                                $maintenance->setEndTs(date_create());
                                $this->entityManager->persist($maintenance);
                            }
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

    /**
     * @param Cars $car
     * @param array|null $damages
     * @return Cars
     */
    public function updateDamages(Cars $car, array $damages = null)
    {
        $car->setDamages($damages);
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        return $car;
    }

    /**
     * Update Car Info
     * 
     * @param Cars $car
     * @param null $company
     * @param null $number
     * @param null $validFrom
     * @param null $expiry
     * @return Cars
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateInsurance(Cars $car, $company = null, $number = null, $validFrom = null, $expiry = null) 
    {
        $carInfo = $car->getCarsInfo();
        $carInfo->setInsuranceCompany($company);
        $carInfo->setInsuranceNumber($number);

        if(!empty($validFrom)) {
            $validFrom = date_create_from_format("d-m-Y h:i:s", $validFrom . " 00:00:00");
        } else {
            $validFrom = null;
        }

        if(!empty($expiry)) {
            $expiry = date_create_from_format("d-m-Y h:i:s", $expiry . " 00:00:00");
        } else {
            $expiry = null;
        }

        $carInfo->setInsuranceValidFrom($validFrom);
        $carInfo->setInsuranceExpiry($expiry);
        
        $this->entityManager->persist($carInfo);
        $this->entityManager->flush();
        
        return $car;
    }
    
    /**
     * @param Cars $car
     */
    public function deleteCar(Cars $car)
    {
        $this->entityManager->remove($car);
        $this->entityManager->flush();
    }

    /**
     * @param string $status
     * @return array
     */
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

    /**
     * @param string $plate
     * @return CarsMaintenance
     */
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

    /**
     * @return CarsDamages
     */
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
    
    /**
     * @return Cars[]
     */
    public function getPublicCarsForAddFreeX($fleet_id)
    {
        $date = new \DateTime("-1 hours");
        return $this->carsRepository->getPublicCarsForAddFreeX($fleet_id, $date);
    }
    
    public function getLastMaintenanceCar($plate){
        return $this->carsMaintenanceRepository->findLastCarsMaintenance($plate);
    }
    
    /**
     * @param CarsMaintenance $oldMaintenance
     * @param Webuser $webuser
     */
    public function closeOldMantenance(CarsMaintenance $oldMaintenance, Webuser $webuser){
        $oldMaintenance->setEndTs(new \DateTime());
        $oldMaintenance->setEndWebuser($webuser);
        
        $this->entityManager->persist($oldMaintenance);
        $this->entityManager->flush();
    }

    
    public function setDirtyCar(Cars $car){ 
        $car->setIntCleanliness("dirty");
        $car->setExtCleanliness("dirty");
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        return $car;
    }
    
    public function setCleanCar(Cars $car){
        $car->setIntCleanliness("clean");
        $car->setExtCleanliness("clean");
        $this->entityManager->persist($car);
        $this->entityManager->flush();
        return $car;
    }
    
    private function positionLInk(Cars $cars){

        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://sharengo.kubris.com/service/plateInfo/" . $cars->getPlate());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        $res = json_decode($output, true);
        curl_close($ch);*/

        $positionLinkOBC = sprintf(
            '<a href="http://maps.google.com/?q=%s,%s" target="_blank">' . $this->translator->translate("Mappa") . '</a>',
            $cars->getLatitude(),
            $cars->getLongitude()
        );
        
        /*if(!is_null($res['data'])){
            $positionLinkBlackBox = sprintf(
                '<br><a href="http://maps.google.com/?q=%s,%s" target="_blank">' . $this->translator->translate("(Black Box)") . '</a>',
                $res['data']['geoLatitude'],
                $res['data']['geoLongitude']
            );
        }else{*/
            $positionLinkBlackBox = '<br><a id ="blackbox-'.$cars->getPlate().'" href="#!" onclick="blackbox(\''.$cars->getPlate().'\')">'.$this->translator->translate("(Black Box)").'</a>';
        //}
        return $positionLinkOBC . $positionLinkBlackBox;
    }
    

}

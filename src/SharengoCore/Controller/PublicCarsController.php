<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\CarsService;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\TripsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class PublicCarsController extends AbstractRestfulController
{

    /**
     * @var CarsService
     */
    private $carsService;

    /**
     * @var ReservationsService
     */
    private $reservationsService;

    /**
     * @var TripsService
     */
    private $tripsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CarsService $carsService,
        ReservationsService $reservationsService,
        TripsService $tripsService,
        DoctrineHydrator $hydrator
    ) {
        $this->carsService = $carsService;
        $this->reservationsService = $reservationsService;
        $this->tripsService = $tripsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnCars = [];

        $cars = $this->carsService->getPublicCars();
        foreach ($cars as $car) {
            if (!$this->isCarReserved($car) && !$this->isCarBusy($car)) {
                $car = $car->toArray($this->hydrator);
                array_push($returnCars, $car);
            }
        }

        return new JsonModel($this->buildReturnData(200, '', $returnCars));
    }

    /**
     * @param Cars
     */
    private function isCarReserved($car)
    {
        $reservations = $this->reservationsService->getActiveReservationsByCar($car->getPlate());
        return !empty($reservations);
    }

    /**
     * @param Cars
     */
    private function isCarBusy($car)
    {
        $reservations = $this->tripsService->getTripsByPlateNotEnded($car->getPlate());
        return !empty($reservations) || $car->getBusy();
    }

    /**
     * @param  integer
     * @param  string
     * @param  mixed[]
     * @return mixed[]
     */
    private function buildReturnData($status, $reason, $data = [])
    {
        $returnData = [];
        $returnData['status'] = $status;
        $returnData['reason'] = $reason;
        $returnData['data'] = $data;
        return $returnData;
    }
}

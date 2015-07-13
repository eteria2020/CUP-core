<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CarsService;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\TripsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CarsController extends AbstractRestfulController
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

        // set filter
        $filters = [];
        $filters['hidden'] = false;

        // get customers
        $cars = $this->carsService->getListCarsFiltered($filters);
        foreach ($cars as $car) {
            $car = $car->toArray($this->hydrator);
            $car = $this->setCarReservation($car);
            $car = $this->setCarBusy($car);
            array_push($returnCars, $car);
        }

        return new JsonModel($this->buildReturnData(200, '', $returnCars));
    }

    public function get($id)
    {
        $car = $this->carsService->getCarByPlate($id);
        $car = $car->toArray($this->hydrator);
        $car = $this->setCarReservation($car);
        $car = $this->setCarBusy($car);

        return new JsonModel($this->buildReturnData(200, '', $car));
    }

    /**
     * @param Cars
     * @return Cars
     */
    private function setCarReservation($car)
    {
        $reservations = $this->reservationsService->getActiveReservationsByCar($car['plate']);
        $car['reservation'] = !empty($reservations);
        return $car;
    }

    /**
     * @param Cars
     * @return Cars
     */
    private function setCarBusy($car)
    {
        $reservations = $this->tripsService->getTripsByPlateNotEnded($car['plate']);
        $car['busy'] = !empty($reservations) || $car['busy'];
        return $car;
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

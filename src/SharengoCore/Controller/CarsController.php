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

    /**
     * Array of ids of Cars out of permitted poligon
     * @var integer[]|null
     */
    private $noGpsCarsIds = null;

    /**
     * @param CarsService $carsService
     * @param ReservationsService $reservationsService
     * @param TripsService $tripsService
     * @param DoctrineHydrator $hydrator
     */
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
            $car = $this->setCarMinutesSinceLastTrip($car);
            array_push($returnCars, $car);
        }
        $returnCars = $this->setCarsGPS($returnCars);

        return new JsonModel($this->buildReturnData(200, '', $returnCars));
    }

    public function get($id)
    {
        $car = $this->carsService->getCarByPlate($id);
        $car = $car->toArray($this->hydrator);
        $car = $this->setCarReservation($car);
        $car = $this->setCarBusy($car);
        $car = $this->setCarMinutesSinceLastTrip($car);
        $car = $this->setCarGps($car);

        return new JsonModel($this->buildReturnData(200, '', $car));
    }

    /**
     * @param Cars $car
     * @return Cars
     */
    private function setCarReservation($car)
    {
        $reservations = $this->reservationsService->getActiveReservationsByCar($car['plate']);
        $car['reservation'] = !empty($reservations);
        return $car;
    }

    /**
     * @param Cars $car
     * @return Cars
     */
    private function setCarBusy($car)
    {
        $trips = $this->tripsService->getTripsByPlateNotEnded($car['plate']);
        $car['busy'] = !empty($trips);
        return $car;
    }

    /**
     * @param Cars $car
     * @return Cars
     */
    private function setCarMinutesSinceLastTrip($car)
    {
        $lastTrip = $this->tripsService->getLastTrip($car['plate']);
        $minutesSinceLastTrip = null;
        if ($lastTrip != null) {
            $minutesSinceLastTrip = (integer) ((time() - $lastTrip->getTimestampEnd()->getTimestamp()) / 60);
        }
        $car['sinceLastTrip'] = $minutesSinceLastTrip;
        return $car;
    }

    /**
     * @param mixed[] $car
     * @return mixed[]
     */
    private function setCarGps($car)
    {
        // if noGpsCarsIds has not yet been filled, proceed with that
        if (is_null($this->noGpsCarsIds)) {
            $carsOutOfBounds = $this->carsService->getCarsOutOfBounds();
            foreach ($carsOutOfBounds as $carOutOfBounds) {
                array_push($this->noGpsCarsIds, $carOutOfBounds->getId());
            }
        }
        $car['gps_ok'] = !in_array($car['id'], $this->noGpsCarsIds);
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

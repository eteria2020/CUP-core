<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CarsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CarsController extends AbstractRestfulController
{

    /**
     * @var CarsService
     */
    private $carsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    /**
     * Array of ids of Cars out of permitted poligon
     * @var string[]|null
     */
    private $noGpsCarsPlates = null;

    /**
     * Array of ids of Cars with active reservation
     * @var string[]|null
     */
    private $reservedCarsPlates = null;

    /**
     * Array of ids of Cars that are being used
     * @var string[]|null
     */
    private $busyCarsPlates = null;

    /**
     * Array of ids of Cars that are being used
     * @var [string => integer]|null
     */
    private $minutesSinceLastTrips = null;

    /**
     * @param CarsService $carsService
     * @param DoctrineHydrator $hydrator
     */
    public function __construct(
        CarsService $carsService,
        DoctrineHydrator $hydrator
    ) {
        $this->carsService = $carsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnCars = [];

        // set filter
        $filters = [];

        // get customers
        $cars = $this->carsService->getListCarsFiltered($filters);
        foreach ($cars as $car) {
            $car = $car->toArray($this->hydrator);
            $car = $this->setCarReservation($car);
            $car = $this->setCarBusy($car);
            $car = $this->setCarMinutesSinceLastTrip($car);
            $car = $this->setCarGps($car);
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
        $car = $this->setCarMinutesSinceLastTrip($car);
        $car = $this->setCarGps($car);

        return new JsonModel($this->buildReturnData(200, '', $car));
    }

    /**
     * @param mixed[] $car
     * @return mixed[]
     */
    private function setCarReservation($car)
    {
        if (is_null($this->reservedCarsPlates)) {
            $this->reservedCarsPlates = $this->carsService->getReservedPlates();
        }
        $car['reservation'] = in_array($car['plate'], $this->reservedCarsPlates);
        return $car;
    }

    /**
     * @param mixed[] $car
     * @return mixed[]
     */
    private function setCarBusy($car)
    {
        if (is_null($this->busyCarsPlates)) {
            $this->busyCarsPlates = $this->carsService->getBusyPlates();
        }
        $car['busy'] = in_array($car['plate'], $this->busyCarsPlates);
        return $car;
    }

    /**
     * @param mixed[] $car
     * @return mixed[]
     */
    private function setCarMinutesSinceLastTrip($car)
    {
        if (is_null($this->minutesSinceLastTrips)) {
            $this->minutesSinceLastTrips = $this->carsService->getMinutesSinceLastTrip();
        }
        $car['sinceLastTrip'] = array_key_exists($car['plate'], $this->minutesSinceLastTrips) ?
            $this->minutesSinceLastTrips[$car['plate']] :
            null;
        return $car;
    }

    /**
     * @param mixed[] $car
     * @return mixed[]
     */
    private function setCarGps($car)
    {
        if (is_null($this->noGpsCarsPlates)) {
            $this->noGpsCarsPlates = $this->carsService->getOutOfBoundsPlates();
        }
        // Checking == true because the value could be null
        $car['gps_ok'] = isset($car['plate'], $this->noGpsCarsPlates)
            && $this->noGpsCarsPlates[$car['plate']];
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

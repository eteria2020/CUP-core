<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CarsService;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\CommandsService;
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
     * @var CommandsService
     */
    private $commandsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CarsService $carsService,
        ReservationsService $reservationsService,
        CommandsService $commandsService,
        DoctrineHydrator $hydrator
    ) {
        $this->carsService = $carsService;
        $this->reservationsService = $reservationsService;
        $this->commandsService = $commandsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnCars = [];

        $cars = $this->carsService->getListCars();
        foreach ($cars as $value) {
            $car = $this->hydrator->extract($value);
            $car = $this->setCarReservation($car);
            array_push($returnCars, $car);
        }

        return new JsonModel($this->buildReturnData(200, '', $returnCars));
    }
 
    public function get($plate)
    {
        $car = $this->carsService->getCarByPlate($plate);

        return new JsonModel($this->buildReturnData(200, '', $car));
    }
 
    /*
    public function update($plate, $data)
    {
        $cmd = '';
        $status = 200;
        $reason = '';

        $car = $this->carsService->getCarByPlate($plate);

        if ($car instanceof Cars) {
            $action = strtolower($data['action']);

            switch ($action) {
                  case 'open' :
                    $cmd = 'OPEN_TRIP';
                    break;
                  case 'close':
                    $cmd = 'CLOSE_TRIP';
                    break;
                  case 'park':
                    $cmd = 'PARK_TRIP';
                    break;
                  case 'unpark':
                    $cmd = 'UNPARK_TRIP';
                    break;

                  default:
                    $reason = "Invalid action";
                    $status = 400;
            }
        }

        if ($status == 200) {
            $this->commandsService->createCommand($plate, true, $cmd);
        }

        return new JsonModel($this->buildReturnData($status, $reason));

    }
    */

    /**
     * @param Cars
     * @return Cars
     */
    private function setCarReservation($car)
    {
        $plate = $car['plate'];
        $reservations = $this->reservationsService->getActiveReservationsByCar($plate);
        $car['reservation'] = !empty($reservations);
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

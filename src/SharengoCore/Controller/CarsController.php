<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CarsService;
use SharengoCore\Service\CommandsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CarsController extends AbstractRestfulController
{

    /**
     * @var CarsService
     */
    private $carsService;

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
        CommandsService $commandsService,
        DoctrineHydrator $hydrator
    ) {
        $this->carsService = $carsService;
        $this->commandsService = $commandsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnCars = [];
        $returnData = [];

        $cars = $this->carsService->getListCars();
        foreach ($cars as $value) {
            array_push($returnCars, $this->hydrator->extract($value));
        }

        $returnData['status'] = 200;
        $returnData['reason'] = '';
        $returnData['data'] = $returnCars;

       return new JsonModel($returnData);
    }
 
    public function get($plate)
    {
        $returnData = [];

        $car = $this->carsService->getCarByPlate($plate);

        $returnData['status'] = 200;
        $returnData['reason'] = '';
        $returnData['data'] = $car;

        return new JsonModel($returnData);
    }
 
    public function update($plate, $data)
    {
        $returnData = [];
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

        $returnData['status'] = $status;
        $returnData['reason'] = $reason;
        $returnData['data'] = [];

        return new JsonModel($returnData);

    }
}

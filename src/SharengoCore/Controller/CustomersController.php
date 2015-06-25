<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\CustomersService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CustomersController extends AbstractRestfulController
{

    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CustomersService $customersService,
        DoctrineHydrator $hydrator
    ) {
        $this->customersService = $customersService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $cars = $this->carsService->getListCars();
        $returnCars = [];
        $returnData = [];

        foreach ($cars as $value) {
            array_push($returnCars, $this->hydrator->extract($value));
        }
        $returnData['data'] = $returnCars;

       return new JsonModel($returnData);
    }
 
    public function get($plate)
    {
        $car = $carsService->getCarByPlate($plate);
        $returnData = [];
        $returnData['data'] = $car;
        return new JsonModel($returnData);
    }
}

<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\CarsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CarsController extends AbstractRestfulController
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var CarsService
     */
    private $carsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        $url,
        CarsService $carsService,
        DoctrineHydrator $hydrator
    ) {
        $this->url = sprintf($url, '');
        $this->carsService = $carsService;
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

<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\CarsService;

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

    public function __construct($url, CarsService $carsService)
    {
        $this->url = sprintf($url, '');
        $this->carsService = $carsService;
    }

    public function getList()
    {
        /*
        $client = new Client($this->url, array(
            'maxredirects' => 0,
            'timeout'      => 30
        ));

        $response = $client->send();

        return new JsonModel(json_decode($response->getBody(), true));
        */
       
       $cars = $this->carsService->getListCars();
       $returnCars = [];
       $car = [];
       $returnData = [];

       foreach ($cars as $value) {
           $car['plate'] = $value->getPlate();
           $car['intCleanliness'] = $value->getIntCleanliness();
           $car['extCleanliness'] = $value->getExtCleanliness();
           $car['battery'] = $value->getBattery();
           $car['busy'] = $value->getBusy();
           $car['status'] = $value->getStatus();
           $car['latitude'] = $value->getLatitude();
           $car['longitude'] = $value->getLongitude();
           array_push($returnCars, $car);
       }
       $returnData['data'] = $returnCars;

       return new JsonModel($returnData);
    }
 
    public function get($id)
    {
        
    }
}

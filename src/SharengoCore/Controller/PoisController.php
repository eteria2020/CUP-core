<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\PoisService;

class PoisController extends AbstractRestfulController
{

    /**
     * @var string
     */
    private $url;

    /**
     * @var PoisService
     */
    private $poisService;

    public function __construct($url, PoisService $poisService)
    {
        $this->url = sprintf($url, '');
        $this->poisService = $poisService;
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
       
       $poisList = $this->poisService->getListPois();
       $returnPois = [];
       $pois = [];
       $returnData = [];

       foreach ($poisList as $value) {
           $pois['lat'] = $value->getLat();
           $pois['lon'] = $value->getLon();
           $pois['type'] = $value->getType();
           $pois['address'] = $value->getAddress();
           array_push($returnPois, $pois);
       }
       $returnData['data'] = $returnPois;

       return new JsonModel($returnData);
    }
}

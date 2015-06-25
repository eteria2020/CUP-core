<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\PoisService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

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

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
      $url,
      PoisService $poisService,
      DoctrineHydrator $hydrator
    ) {
        $this->url = sprintf($url, '');
        $this->poisService = $poisService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
       $poisList = $this->poisService->getListPois();
       $returnPois = [];
       $pois = [];
       $returnData = [];

       foreach ($poisList as $value) {
           array_push($returnPois, $this->hydrator->extract($value));
       }
       $returnData['data'] = $returnPois;

       return new JsonModel($returnData);
    }
}

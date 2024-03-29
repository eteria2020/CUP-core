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
     * @var PoisService
     */
    private $poisService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        PoisService $poisService,
        DoctrineHydrator $hydrator
    ) {
        $this->poisService = $poisService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $poisList = $this->poisService->getListPois();
        $returnPois = [];
        $pois = [];

        foreach ($poisList as $value) {
            array_push($returnPois, $this->hydrator->extract($value));
        }

        return new JsonModel($this->buildReturnData(200, '', $returnPois));
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

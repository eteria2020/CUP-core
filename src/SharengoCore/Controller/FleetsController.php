<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CarsService;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\TripsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class FleetsController extends AbstractRestfulController
{

    /**
     * @var CarsService
     */
    private $carsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CarsService $carsService,
        DoctrineHydrator $hydrator
    ) {
        $this->carsService = $carsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnFleets = [];

        // get fleets
        $fleets = $this->carsService->getFleets();
        foreach ($fleets as $fleet) {
            array_push($returnFleets, $fleet->toArray($this->hydrator));
        }

        return new JsonModel($this->buildReturnData(200, '', $returnFleets));
    }

    public function get($id)
    {
        $fleet = $this->carsService->getFleet($id)->toArray($this->hydrator);
        
        return new JsonModel($this->buildReturnData(200, '', $fleet));
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

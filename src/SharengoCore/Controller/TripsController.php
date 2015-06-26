<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\TripsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class TripsController extends AbstractRestfulController
{

    /**
     * @var TripService
     */
    private $tripsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        TripsService $tripsService,
        DoctrineHydrator $hydrator
    ) {
        $this->tripsService = $tripsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $tripsList = $this->tripsService->getListTrips();
        $returnTrips = [];
        $trips = [];

        foreach ($tripsList as $value) {
            array_push($returnTrips, $this->hydrator->extract($value));
        }

        return new JsonModel(buildReturnData(200, '', $returnTrips));
    }
 
    public function get($tripId)
    {
        $trip = $this->tripsService->getTripById($tripId);

        return new JsonModel(buildReturnData(200, '', $trip));
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

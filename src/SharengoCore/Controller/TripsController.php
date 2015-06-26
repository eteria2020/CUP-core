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
        $returnData = [];

        foreach ($tripsList as $value) {
            array_push($returnTrips, $this->hydrator->extract($value));
        }

        $returnData['status'] = 200;
        $returnData['reason'] = '';
        $returnData['data'] = $returnTrips;

        return new JsonModel($returnData);
    }
}

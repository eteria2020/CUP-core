<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\TripsService;
use SharengoCore\Service\CustomersService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class TripsController extends AbstractRestfulController
{

    /**
     * @var ContService
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
        $returnTrips = [];
        $trips = [];

        // get limit
        $limit = $this->params()->fromQuery('limit');
        if ($limit === null || $limit > 10 || $limit <= 0) {
            $limit = 10;
        }

        // get filters
        $filters = [];
        if ($this->params()->fromQuery('plate') !== null) {
            $filters['car'] = $this->params()->fromQuery('plate');
        }
        if ($this->params()->fromQuery('user') !== null) {
            $user = $this->params()->fromQuery('user');
            if (is_numeric($user)) {
                $filters['customer'] = $user;
            }
        }

        // get trips
        $trips = $this->tripsService->getListTripsFilteredLimited($filters, $limit);

        foreach ($trips as $value) {
            $trip = $this->tripsService->toArray($value);
            array_push($returnTrips, $trip);
        }

        return new JsonModel($this->buildReturnData(200, '', $returnTrips));
    }
 
    public function get($id)
    {
        $trip = $this->tripsService->getTripById($id);
        if ($trip === null) {
            $trip = [];
        }

        return new JsonModel($this->buildReturnData(200, '', $trip));
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

    /*
    /trips/of-car/:plate -> diventa -> /trips?plate=83497
    /trips/last-closed-of-car/:plate -> diventa -> /trips?limit=1&plate=2346
    /trips/last-user-trips/:user -> diventa -> /trips?user=pippo
    /trips/of-user/:user -> diventa -> /trips?limit=1&user=pippo
     */
}

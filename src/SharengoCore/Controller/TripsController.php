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

        // param flags
        $isPlateSet = false;
        $isCustomerSet = false;

        // get limit
        $limit = $this->params()->fromQuery('limit');
        if ($limit === null || $limit <= 0) {
            $limit = 1;
        }

        // get filters
        $filters = [];
        if ($this->params()->fromQuery('plate') !== null) {
            $filters['car'] = $this->params()->fromQuery('plate');
            $isPlateSet = true;
        }
        if ($this->params()->fromQuery('customer') !== null) {
            $user = $this->params()->fromQuery('customer');
            if (is_numeric($user)) {
                $filters['customer'] = $user;
                $isCustomerSet = true;
            }
        }

        // get trips
        $trips = [];
        if ($this->params()->fromQuery('running') == true) {
            if ($isPlateSet) {
                $trips = $this->tripsService->getTripsByPlateNotEnded($filters['car']);
            } elseif ($isCustomerSet) {
                $trips = $this->tripsService->getTripsByCustomerNotEnded($filters['customer']);
            } else {
                $trips = $this->tripsService->getListTripsFilteredLimited($filters, $limit);
            }
        } else {
            $trips = $this->tripsService->getListTripsFilteredLimited($filters, $limit);
        }

        // parse trips
        foreach ($trips as $trip) {
            $trip = $trip->toArray($this->hydrator);
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
}

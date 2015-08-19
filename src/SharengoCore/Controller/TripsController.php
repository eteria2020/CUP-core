<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\TripsService;
use SharengoCore\Service\TripPaymentsService;
use SharengoCore\Service\CustomersService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Authentication\AuthenticationService;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripBills;

class TripsController extends AbstractRestfulController
{
    /**
     * @var TripsService
     */
    private $tripsService;

    /**
     * @var TripPaymentsService
     */
    private $tripPaymentsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    /**
     * @var AuthenticationService
     */
    private $authService;

    public function __construct(
        TripsService $tripsService,
        TripPaymentsService $tripPaymentsService,
        DoctrineHydrator $hydrator,
        AuthenticationService $authService
    ) {
        $this->tripsService = $tripsService;
        $this->tripPaymentsService = $tripPaymentsService;
        $this->hydrator = $hydrator;
        $this->authService = $authService;
    }

    public function getList()
    {
        $returnTrips = [];
        $trips = [];

        // param flags
        $isPlateSet = false;
        $isCustomerSet = false;
        $isMonthSet = false;

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

        $user = $this->authService->getIdentity();
        if ($this->params()->fromQuery('month') != null) {
            $filters['month'] = $this->params()->fromQuery('month');
            $isMonthSet = true;
            if ($user instanceof Customers) {
                $filters['customer'] = $user->getId();
            }
        } else {
            if ($user instanceof Customers) {
                return new JsonModel($this->buildReturnData(403, '', []));
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
        } elseif ($isMonthSet) {
            $trips = $this->tripsService->getListTripsForMonthByCustomer($filters['month'], $filters['customer']);
            // parse trips with tripPayments
            foreach ($trips as $key => $trip) {
                // get all data needed...tripPayments, tripBonuses and tripFreeFares
                //$trip = $trip->toArray($this->hydrator);
                array_push($returnTrips, $trip);
            }
            return new JsonModel($this->buildReturnData(200, '', $returnTrips));
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

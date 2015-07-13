<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\CarsService;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\TripsService;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class PublicCarsController extends AbstractRestfulController
{

    /**
     * @var CarsService
     */
    private $carsService;

    /**
     * @var ReservationsService
     */
    private $reservationsService;

    /**
     * @var TripsService
     */
    private $tripsService;

    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CarsService $carsService,
        ReservationsService $reservationsService,
        TripsService $tripsService,
        AuthenticationService $authService,
        DoctrineHydrator $hydrator
    ) {
        $this->carsService = $carsService;
        $this->reservationsService = $reservationsService;
        $this->tripsService = $tripsService;
        $this->authService = $authService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnCars = [];

        // get user id from AuthService
        $user = $this->authService->getIdentity();
        $userId = '';
        if ($user instanceof Customers) {
            $userId = $user->getId();
        }

        $cars = $this->carsService->getPublicCars();
        foreach ($cars as $car) {
            if ($this->isCarAvailable($car, $userId)) {
                $car = $car->toArray($this->hydrator);
                array_push($returnCars, $car);
            }
        }
        return new JsonModel($this->buildReturnData(200, '', $returnCars));
    }

    /**
     * @param  Cars  $car
     * @param  string  $userId
     * @return boolean
     */
    private function isCarAvailable($car, $userId)
    {
        // check for active reservations
        $reservations = $this->reservationsService->getActiveReservationsByCar($car->getPlate());
        $isReservedByCurrentUser = false;
        if (!empty($reservations)) {
            $customer = $reservations[0]->getCustomer();
            if ($customer !== null) {
                $isReservedByCurrentUser = $customer->getId() == $userId;
            }
        }
        $isReserved = !empty($reservations);
        // check for active trips and busy param
        $reservations = $this->tripsService->getTripsByPlateNotEnded($car->getPlate());
        $isBusy = !empty($reservations) || $car->getBusy();

        return (!$isReserved && !$isBusy) || $isReservedByCurrentUser;
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

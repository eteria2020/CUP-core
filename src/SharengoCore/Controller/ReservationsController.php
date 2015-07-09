<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\CarsService;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ReservationsController extends AbstractRestfulController
{

    const MAXRESERVATIONS = 1;

    /**
     * @var ReservationsService
     */
    private $reservationsService;

    /**
     * @var CarsService
     */
    private $carsService;

    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        ReservationsService $reservationsService,
        CarsService $carsService,
        AuthenticationService $authService,
        DoctrineHydrator $hydrator
    ) {
        $this->reservationsService = $reservationsService;
        $this->carsService = $carsService;
        $this->authService = $authService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $status = 200;
        $reason = 'OK';

        $returnReservations = [];

        // get filters
        $filters = [];
        if ($this->params()->fromQuery('plate') !== null) {
            $filters['car'] = $this->params()->fromQuery('plate');
        }
        if ($this->params()->fromQuery('active') !== null) {
            $filters['active'] = $this->params()->fromQuery('active');
        }

        // get reservations
        $reservations = $this->reservationsService->getListReservationsFiltered($filters);

        // process reservations
        foreach ($reservations as $reservation) {
            $reservation = $reservation->toArray($this->hydrator);
            array_push($returnReservations, $reservation);
        }

        return new JsonModel($this->buildReturnData($status, $reason, $returnReservations));
    }

    public function get($id)
    {
        return new JsonModel([]);
    }

    public function create($data)
    {
        $status = 200;
        $reason = 'OK';

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        // check if user has already active reservations
        $reservations = $this->reservationsService->getActiveReservationsByCustomer($user);
        if (count($reservations) < self::MAXRESERVATIONS) {

            $plate = $data['plate'];
            if ($plate !== null) {

                $car = $this->carsService->getCarByPlate($plate);
                if ($car instanceof SharengoCore\Entity\Cars) {

                    if (!$this->reservationsService->reserveCarForCustomer($car, $user)) {
                        $reason = "L'auto è già occupata";
                    }

                } else {
                    $reason = '';
                }

            } else {
                $reason = '';
            }

        } else {
            $reason = 'Hai già una prenotazione attiva';
        }

        return new JsonModel($this->buildReturnData($status, $reason));
    }

    public function update($id, $data)
    {
        $status = 200;
        $reason = 'OK';

        return new JsonModel($this->buildReturnData($status, $reason));
    }

    public function delete($id)
    {
        $status = 200;
        $reason = 'OK';

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        if (!$this->reservationsService->removeCustomerReservationWithId($user, $id)) {
            $reason = 'reservation not found';
        }

        return new JsonModel($this->buildReturnData($status, $reason));
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

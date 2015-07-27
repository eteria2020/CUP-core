<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\CarsService;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ReservationsController extends AbstractRestfulController
{

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
        $filters['car'] = $this->params()->fromQuery('plate');
        $filters['active'] = true;

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
        $reason = '';

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        $plate = $data['plate'];
        if ($plate !== null) {

            if ($user->getEnabled()) {

                $car = $this->carsService->getCarByPlate($plate);
                if ($car instanceof Cars && $car->getStatus() == 'operative') {

                    // check if user has already active reservations
                    if (!$this->reservationsService->hasActiveReservationsByCustomer($user)) {

                        if (!$this->reservationsService->reserveCarForCustomer($car, $user)) {
                            $reason = "L'auto è già occupata";
                            $status = 210;
                        } else {
                            $reason = "Prenotazione effettuata";
                        }

                    } else {
                        $reason = 'Hai già una prenotazione attiva';
                        $status = 211;
                    }

                } else {
                    $reason = "L'auto non è al momento disponibile";
                    $status = 212;
                }

            } else {
                $reason = "Prenotazioni non disponibili per questo utente";
                $status = 213;
            }

        } else {
            $reason = "Si è verificato un errore, riprovare più tardi";
            $status = 214;
        }

        return new JsonModel($this->buildReturnData($status, $reason));
    }

    public function update($id, $data)
    {
        $status = 501;
        $reason = '';

        return new JsonModel($this->buildReturnData($status, $reason));
    }

    public function delete($id)
    {
        $status = 200;
        $reason = 'OK';

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        if (!$this->reservationsService->removeCustomerReservationWithId($user, $id)) {
            $reason = 'Si è verificato un errore, riprovare più tardi';
            $status = 210;
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

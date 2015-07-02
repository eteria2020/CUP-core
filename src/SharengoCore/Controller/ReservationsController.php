<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\Reservations;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\ReservationsService;
use SharengoCore\Service\CarsService;
use Zend\Authentication\AuthenticationService as AuthenticationService;
use Doctrine\ORM\EntityManager;
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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        ReservationsService $reservationsService,
        CarsService $carsService,
        AuthenticationService $authService,
        EntityManager $entityManager,
        DoctrineHydrator $hydrator
    ) {
        $this->reservationsService = $reservationsService;
        $this->carsService = $carsService;
        $this->authService = $authService;
        $this->entityManager = $entityManager;
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

        if ($user instanceof Customers) {
            // check if user has already active reservations
            $reservations = $this->reservationsService->getActiveReservationsByCustomer($user);

            if (count($reservations) < self::MAXRESERVATIONS) {

                // get car from $data
                $plate = $data['plate'];
                if ($plate !== null) {

                    $car = $this->carsService->getCarByPlate($plate);

                    if ($car !== null) {

                        // get card from user
                        $card = $user->getCard();
                        $card = json_encode(($card !== null) ? [$card->getCode()] : []);

                        // create reservation for user
                        $reservation = new Reservations();
                        $reservation->setTs(date_create())
                                ->setCar($car)
                                ->setCustomer($user)
                                ->setBeginningTs(date_create())
                                ->setActive(true)
                                ->setLength(30)
                                ->setToSend(true)
                                ->setCards($card);

                        // persist and flush reservation
                        $this->entityManager->persist($reservation);
                        $this->entityManager->flush();

                    } else {
                        $reason = 'car does not exist';
                    }

                } else {
                    $reason = 'no car specified';
                }

            } else {
                $reason = 'max active reservations for user reached';
            }

        } else {
            // admin & callcenter
        }

        return new JsonModel($this->buildReturnData($status, $reason));
    }
 
    public function update($id, $data)
    {
        $status = 200;
        $reason = 'OK';

        $reservationFound = false;

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        if ($user instanceof Customers) {
            // get user's active reservations
            $reservations = $this->reservationsService->getActiveReservationsByCustomer($user);

            foreach ($reservations as $reservation) {

                if ($reservation->getId() == $id) {

                    $reservationFound = true;

                    $reservation->setActive(false)
                            ->setToSend(true);

                    // persist and flush reservation
                    $this->entityManager->persist($reservation);
                    $this->entityManager->flush();

                    break;
                }

            }

            if (!$reservationFound) {
                $reason = 'reservation not found';
            }

        } else {
            // admin & callcenter
        }

        return new JsonModel($this->buildReturnData($status, $reason));
    }
 
    public function delete($id)
    {
        $status = 200;
        $reason = 'OK';

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

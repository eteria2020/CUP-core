<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\ReservationsRepository;
use SharengoCore\Entity\Reservations;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CustomersService;
use SharengoCore\Entity\Customers;

class ReservationsService
{
    /**
     * @var  ReservationsRepository
     */
    private $reservationsRepository;

    /**
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @var CustomerService
     */
    private $customersService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param ReservationsRepository $reservationsRepository
     * @param DatatableService $datatableService
     * @param CustomersService $customersService
     * @param EntityManager $entityManager
     */
    public function __construct(
        ReservationsRepository $reservationsRepository,
        DatatableService $datatableService,
        CustomersService $customersService,
        EntityManager $entityManager
    ) {
        $this->reservationsRepository = $reservationsRepository;
        $this->datatableService = $datatableService;
        $this->customersService = $customersService;
        $this->entityManager = $entityManager;
    }

    public function getListReservationsFiltered($filters = [])
    {
        return $this->reservationsRepository->findBy($filters);
    }

    public function getActiveReservationsByCar($plate)
    {
        return $this->reservationsRepository->findActiveReservationsByCar($plate);
    }

    public function getActiveReservationsByCustomer($customer)
    {
        return $this->reservationsRepository->findActiveReservationsByCustomer($customer);
    }

    public function getTotalReservations()
    {
        return $this->reservationsRepository->getTotalReservations();
    }

    public function getReservationsToDelete()
    {
        return $this->reservationsRepository->findReservationsToDelete();
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $reservations = $this->datatableService->getData('Reservations', $as_filters);

        return array_map(function (Reservations $reservation) {

            return [
                'e' => [
                    'id'       => $reservation->getId(),
                    'carPlate' => $reservation->getCar()->getPlate(),
                    'customer' => null != $reservation->getCustomer() ? $reservation->getCustomer()->getName() . ' ' . $reservation->getCustomer()->getSurname() : '',
                    'cards'    => $reservation->getCards(),
                    'active'   => $reservation->getActive() ? 'Si' : 'No',
                ]
            ];
        }, $reservations);
    }

    public function getMaintenanceReservation($plate)
    {
        return $this->reservationsRepository->findOneBy(array('car' => $plate,
                                                              'length' => -1,
                                                              'customer' => null));
    }

    public function createMaintenanceReservation(Cars $car) {

        $maintainersCardCodes = $this->customersService->getListMaintainersCards();
        $cardsArray = [];
        foreach ($maintainersCardCodes as $cardCode) {
            array_push($cardsArray, $cardCode['1']);
        }
        $cardsString = json_encode($cardsArray);

        $reservation = Reservations::createMaintenanceReservation($car, $cardsString);
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

    }

    /**
     * @param string $plate
     * @param Customers $customer
     * @return boolean returns true if successful, returns false otherwise
     */
    public function reserveCarForCustomer(Cars $car, Customers $customer)
    {
        if (count($this->getActiveReservationsByCar($car->getPlate())) == 0) {
            // get card from user
            $card = $customer->getCard();
            $card = json_encode(($card !== null) ? [$card->getCode()] : []);

            // create reservation for user
            $reservation = new Reservations();
            $reservation->setTs(date_create())
                    ->setCar($car)
                    ->setCustomer($customer)
                    ->setBeginningTs(date_create())
                    ->setActive(true)
                    ->setLength(1800)
                    ->setToSend(true)
                    ->setCards($card);

            // persist and flush reservation
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param  Customers $customer
     * @param  integer $id
     * @return boolean returns true if successful, returns false otherwise
     */
    public function removeCustomerReservationWithId(Customers $customer, $id)
    {
        // get user's active reservations
        $reservations = $this->getActiveReservationsByCustomer($customer);

        foreach ($reservations as $reservation) {

            if ($reservation->getId() == $id) {

                $reservation->setActive(false)
                        ->setToSend(true)
                        ->setDeletedTs(date_create());

                // persist and flush reservation
                $this->entityManager->persist($reservation);
                $this->entityManager->flush();

                return true;
            }

        }

        return false;

    }

}

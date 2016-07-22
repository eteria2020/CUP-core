<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\ReservationsRepository;
use SharengoCore\Entity\Reservations;
use SharengoCore\Service\CustomersService;
use SharengoCore\Service\DatatableServiceInterface;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\I18n\Translator;

class ReservationsService
{
    /**
     * @const integer
     */
    const MAX_RESERVATIONS = 1;

    /**
     * @const integer
     */
    const SYS_RESERVATION_LENGTH = -1;

    /**
     * @var ReservationsRepository
     */
    private $reservationsRepository;

    /**
     * @var DatatableServiceInterface
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
     * @var Translator
     */
    private $translator;

    /**
     * @param ReservationsRepository $reservationsRepository
     * @param DatatableServiceInterface $datatableService
     * @param CustomersService $customersService
     * @param EntityManager $entityManager
     */
    public function __construct(
        ReservationsRepository $reservationsRepository,
        DatatableServiceInterface $datatableService,
        CustomersService $customersService,
        EntityManager $entityManager,
        Translator $translator
    ) {
        $this->reservationsRepository = $reservationsRepository;
        $this->datatableService = $datatableService;
        $this->customersService = $customersService;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @param array|null $filters
     * @return Reservations[]
     */
    public function getListReservationsFiltered($filters = [])
    {
        return $this->reservationsRepository->findBy($filters);
    }

    /**
     * @param string $plate
     * @return Reservations[]
     */
    public function getActiveReservationsByCar($plate)
    {
        return $this->reservationsRepository->findActiveReservationsByCar($plate);
    }

    /**
     * @param Customers $customer
     * @return Reservations[]
     */
    public function getActiveReservationsByCustomer($customer)
    {
        return $this->reservationsRepository->findActiveReservationsByCustomer($customer);
    }

    /**
     * @param Customers $customer
     * @return boolean
     */
    public function hasActiveReservationsByCustomer($customer)
    {
        return count($this->getActiveReservationsByCustomer($customer)) >= self::MAX_RESERVATIONS;
    }

    /**
     * @return integer
     */
    public function getTotalReservations()
    {
        return $this->reservationsRepository->getTotalReservations();
    }

    /**
     * @return Reservations[]
     */
    public function getReservationsToDelete()
    {
        return $this->reservationsRepository->findReservationsToDelete();
    }

    /**
     * @param array|null $as_filters
     * @param boolean|null $count
     * @return mixed[]|integer
     */
    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $reservations = $this->datatableService->getData('Reservations', $as_filters, $count);

        if ($count) {
            return $reservations;
        }

        return array_map(function (Reservations $reservation) {

            return [
                'e' => [
                    'id'       => $reservation->getId(),
                    'carPlate' => $reservation->getCar()->getPlate(),
                    'customer' => null != $reservation->getCustomer() ? $reservation->getCustomer()->getName() . ' ' . $reservation->getCustomer()->getSurname() : '',
                    'customerId' => null != $reservation->getCustomer() ? $reservation->getCustomer()->getId() : '',
                    'cards'    => ($reservation->getLength() != self::SYS_RESERVATION_LENGTH) ? $reservation->getCards() : $this->translator->translate('PRENOTAZIONE DI SISTEMA'),
                    'active'   => $reservation->getActive() ? $this->translator->translate('Si') : $this->translator->translate('No'),
                ]
            ];
        }, $reservations);
    }

    /**
     * @param string $plate
     * @return mixed[]
     */
    public function getMaintenanceReservation($plate)
    {
        return $this->reservationsRepository->findOneBy([
            'car' => $plate,
            'length' => -1,
            'customer' => null
        ]);
    }

    /**
     * @param Cars $car
     * @param boolean $saveToDb
     * @return Reservations
     */
    public function createMaintenanceReservation(Cars $car, $saveToDb = true)
    {
        // Get all maintainers codes
        $maintainersCardCodes = $this->customersService->getListMaintainersCards();

        // Create single json string with all maintainer's card codes
        $cardsArray = [];
        foreach ($maintainersCardCodes as $cardCode) {
            array_push($cardsArray, $cardCode['1']);
        }
        $cardsString = json_encode($cardsArray);

        // Create the reservation
        $reservation = Reservations::createMaintenanceReservation(
            $car,
            $cardsString
        );

        if ($saveToDb) {
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
        }

        return $reservation;
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
     * @param Customers $customer
     * @param integer $id
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

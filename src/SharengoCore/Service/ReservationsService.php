<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\ReservationsRepository;
use SharengoCore\Entity\Reservations;
use SharengoCore\Entity\Cars;
use SharengoCore\Service\CustomersService;


class ReservationsService
{
    /** @var EntityManager */
    private $entityManager;
    
    /** @var  ReservationsRepository */
    private $reservationsRepository;

    /** @var DatatableService */
    private $datatableService;

    /** @var CustomerService */
    private $customersService;

    /**
     * @param ReservationsRepository $reservationsRepository
     */
    public function __construct(
        EntityManager $entityManager,
        ReservationsRepository $reservationsRepository,
        DatatableService $datatableService,
        CustomersService $customersService)
    {
        $this->entityManager = $entityManager;
        $this->reservationsRepository = $reservationsRepository;
        $this->datatableService = $datatableService;
        $this->customersService = $customersService;
    }

    public function getActiveReservationsByCar($plate)
    {
        return $this->reservationsRepository->findActiveReservationsByCar($plate);
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

}
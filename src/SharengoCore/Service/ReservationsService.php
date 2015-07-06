<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\ReservationsRepository;
use SharengoCore\Entity\Reservations;


class ReservationsService
{
    /** @var  ReservationsRepository */
    private $reservationsRepository;

    /** @var DatatableService */
    private $datatableService;

    /**
     * @param ReservationsRepository $reservationsRepository
     */
    public function __construct(ReservationsRepository $reservationsRepository, DatatableService $datatableService)
    {
        $this->reservationsRepository = $reservationsRepository;
        $this->datatableService = $datatableService;
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
        return $this->reservationsRepository->findOneBy(array('car_plate' => $plate,
                                                              'length' => -1,
                                                              'customer' => null));
    }

}
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

    public function getListReservationsFiltered($filters = [])
    {
        return $this->reservationsRepository->findBy($filters);
    }

    public function getActiveReservationsByCar($plate)
    {
        return $this->reservationsRepository->findActiveReservationsByCar($plate);
    }

    public function getTotalReservations()
    {
        return $this->reservationsRepository->getTotalReservations();
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $reservations = $this->datatableService->getData('Reservations', $as_filters);

        return array_map(function (Reservations $reservation) {

            return [
                'e' => [
                    'id'       => $reservation->getId(),
                    'carPlate' => $reservation->getCar()->getPlate(),
                    'customer' => $reservation->getCustomer()->getName() . ' ' . $reservation->getCustomer()->getSurname(),
                    'cards'    => $reservation->getCards(),
                    'active'   => $reservation->getActive() ? 'Si' : 'No',
                ]
            ];
        }, $reservations);
    }

}
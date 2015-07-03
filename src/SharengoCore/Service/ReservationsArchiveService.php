<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\ReservationsArchiveRepository;
use SharengoCore\Entity\ReservationsArchive;
use SharengoCore\Entity\Reservations;


class ReservationsArchiveService
{
    /** @var  ReservationsArchiveRepository */
    private $reservationsArchiveRepository;

    /**
     * @param ReservationsArchiveRepository $reservationsArchiveRepository
     */
    public function __construct(ReservationsArchiveRepository $reservationsArchiveRepository)
    {
        $this->reservationsArchiveRepository = $reservationsArchiveRepository;
    }

    public function getActiveReservationsArchiveByCar($plate)
    {
        return $this->reservationsArchiveRepository->findActiveReservationsArchiveByCar($plate);
    }

    public function getReservationsArchiveFromReservation(Reservations $reservation, $reason)
    {
        $reservationsArchive = new ReservationsArchive();

        $reservationsArchive->setTs($reservation->getTs());
        $reservationsArchive->setBeginningTs($reservation->getBeginningTs());
        $reservationsArchive->setActive($reservation->getActive());
        $reservationsArchive->setCards($reservation->getCards());
        $reservationsArchive->setLength($reservation->getLength());
        $reservationsArchive->setToSend($reservation->getToSend());
        $reservationsArchive->setSentTs($reservation->getSentTs());
        $reservationsArchive->setCustomer($reservation->getCustomer());
        $reservationsArchive->setCar($reservation->getCar());
        $reservationsArchive->setConsumedTs($reservation->getConsumedTs());
        $reservationsArchive->setReason($reason);
        $reservationsArchive->setArchivedTs(date_create());

        return $reservationsArchive;
    }

}

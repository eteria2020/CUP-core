<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripFreeFaresRepository;
use SharengoCore\Entity\TripFreeFares;
use SharengoCore\Entity\Trips;

use Doctrine\ORM\EntityManager;

class TripFreeFaresService
{
    /**
     * @var TripFreeFares
     */
    private $tripFreeFaresRepository;

    /**
     * @param TripFreeFaresRepository
     */
    public function __construct(
        TripFreeFaresRepository $tripFreeFaresRepository
    ) {
        $this->tripFreeFaresRepository = $tripFreeFaresRepository;
    }

    /**
     * @param integer $id
     * @return TripFreeFares
     */
    public function getTripFreeFaresById($id)
    {
        return $this->tripFreeFaresRepository->findOneById($id);
    }

    /**
     * @param Trips $trip
     * @return TripFreeFares[]
     */
    public function getFreeFaresByTrip(Trips $trip)
    {
        return $this->tripFreeFaresRepository->findByTrip($trip);
    }
}

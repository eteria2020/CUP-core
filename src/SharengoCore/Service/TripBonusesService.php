<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripBonusesRepository;
use SharengoCore\Entity\TripBonuses;
use SharengoCore\Entity\Trips;

use Doctrine\ORM\EntityManager;

class TripBonusesService
{
    /**
     * @var TripBonuses
     */
    private $tripBonusesRepository;

    /**
     * @param TripBonusesRepository
     */
    public function __construct(
        TripBonusesRepository $tripBonusesRepository
    ) {
        $this->tripBonusesRepository = $tripBonusesRepository;
    }

    /**
     * @param integer $id
     * @return TripBonuses
     */
    public function getTripBonusesById($id)
    {
        return $this->tripBonusesRepository->findOneById($id);
    }

    /**
     * @param Trips $trip
     * @return TripBonuses[]
     */
    public function getBonusesByTrip(Trips $trip)
    {
        return $this->tripBonusesRepository->findByTrip($trip);
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripBillsRepository;
use SharengoCore\Entity\Repository\TripBonusesRepository;
use SharengoCore\Entity\Repository\TripFreeFaresRepository;

use Doctrine\ORM\EntityManager;

class AccountedTripsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TripBillsRepository
     */
    private $tripBillsRepository;

    /**
     * @var TripBonusesRepository
     */
    private $tripBonusesRepository;

    /**
     * @var TripFreeFaresRepository
     */
    private $tripFreeFaresRepository;

    public function __construct(
        EntityManager $entityManager,
        TripBillsRepository $tripBillsRepository,
        TripBonusesRepository $tripBonusesRepository,
        TripFreeFaresRepository $tripFreeFaresRepository
    ) {
        $this->entityManager = $entityManager;
        $this->tripBillsRepository = $tripBillsRepository;
        $this->tripBonusesRepository = $tripBonusesRepository;
        $this->tripFreeFaresRepository = $tripFreeFaresRepository;
    }

    /**
     * remove from the tables of accounted trips the trips given by the ids
     *
     * @param int[] array of trip ids
     */
    public function removeAccountedTrips(array $tripIds)
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->tripBillsRepository->deleteTripBillsByTripIds($tripIds);
            $this->tripBonusesRepository->deleteTripBonusesByTripIds($tripIds);
            $this->tripFreeFaresRepository->deleteTripFreeFaresByTripIds($tripIds);

            $this->entityManager->getConnection()->commit();
        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollback();
            throw $e;
        }
    }
    
    public function findFreeMinutesByTripIdFromTripFreeFraes($tripId) {
        return $this->tripFreeFaresRepository->findByTripId($tripId);
    }
    
    public function findFreeMinutesByTripIdFromTripBonuses($tripId) {
        return $this->tripBonusesRepository->findByTripId($tripId);
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CarsBonusHistoryRepository;
use SharengoCore\Entity\CarsBonusHistory;

use Doctrine\ORM\EntityManager;

class CarsBonusHistoryService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CarsBonusHistoryRepository
     */
    private $carsBonusHistoryRepository;


    /**
     * @param EntityManager $entityManager
     * @param CarsBonusHistoryRepository $carsBonusHistoryRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CarsBonusHistoryRepository $carsBonusHistoryRepository
    ){
        $this->entityManager = $entityManager;
        $this->carsBonusHistoryRepository = $carsBonusHistoryRepository;
    }
    
    public function createRecord($freeX, $permanance, $car)
    {
        $cars_bonus_history = new CarsBonusHistory($freeX, $permanance, $car);
        $this->entityManager->persist($cars_bonus_history);
        $this->entityManager->flush($cars_bonus_history);
    }
    
    public function deleteOldRecord()
    {
        $date = new \DateTime("-3 months");
        return $this->carsBonusHistoryRepository->deleteOldRecord($date);
    }
}

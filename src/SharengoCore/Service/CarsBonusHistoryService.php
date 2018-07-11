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
    private $carsBonusHistyìoryRepository;


    /**
     * @param EntityManager $entityManager
     * @param CarsBonusRepository $carsBonusRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CarsBonusHistoryRepository $carsBonusHistyìoryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->$carsBonusHistyìoryRepository = $carsBonusHistyìoryRepository;
    }
    
    public function createRecord($freeX, $permanance, $plate)
    {
        $cars_bonus_history = new CarsBonusHistory($freeX, $permanance, $plate);
        $this->entityManager->persist($cars_bonus_history);
        $this->entityManager->flush($cars_bonus_history);
    }
    
}

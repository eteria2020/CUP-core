<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CarsBonusRepository;
use SharengoCore\Entity\CarsBonus;

use Doctrine\ORM\EntityManager;

class CarsBonusService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CarsBonusRepository
     */
    private $carsBonusRepository;


    /**
     * @param EntityManager $entityManager
     * @param CarsBonusRepository $carsBonusRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CarsBonusRepository $carsBonusRepository
    ) {
        $this->entityManager = $entityManager;
        $this->carsBonusRepository = $carsBonusRepository;
    }

    public function findOneByPLate($plate)
    {
        return $this->carsBonusRepository->findByPLate($plate);
    }
    
    public function addFreeBonus(CarsBonus $car_bonus, $val)
    {
        $car_bonus->setFreeX($val);
        $this->entityManager->persist($car_bonus);
        $this->entityManager->flush($car_bonus);
        return $car_bonus;
    }

}

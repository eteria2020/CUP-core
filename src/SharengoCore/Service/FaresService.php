<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Fares;
use SharengoCore\Entity\Repository\FaresRepository;

class FaresService
{
    const MOTION_COST_PER_MINUTE = 28;
    const PARK_COST_PER_MINUTE = 10;

    private $entityManager;

    /**
     * @var FaresRepository
     */
    private $faresRepository;

    public function __construct(
        FaresRepository $faresRepository,
        EntityManager $entityManager
    )
    {
        $this->faresRepository = $faresRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * at the moment there is only one fare, so we return that one. When there
     * will be more than one fare, this will have input parameters to decide
     * which fare to consider
     *
     * @return Fares
     */
    public function getFare()
    {
        return $this->faresRepository->findOne();
    }

    /**
     * Persists $fareData
     *
     * @return boolean
     */
    public function saveData($fareData)
    {
        $newFares = $fareData['fares'];
        $newCostSteps = [
            1440 => $newFares['costStep1440'],
            240 =>  $newFares['costStep240'],
            60 =>   $newFares['costStep60']
        ];

        $newFare = new Fares(self::MOTION_COST_PER_MINUTE, self::PARK_COST_PER_MINUTE, json_encode($newCostSteps));
        $currentFare = $this->getFare();

        if ($newFare->getCostSteps() != $currentFare->getCostSteps()) {
            $this->entityManager->persist($newFare);
            $this->entityManager->flush();
            return true;
        } else {
            return false;
        }
    }
}

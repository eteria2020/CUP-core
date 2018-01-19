<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\AllPenalties;
use SharengoCore\Entity\Queries\AllCausal;

use Doctrine\ORM\EntityManager;

class PenaltiesService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Penalties[]
     */
    public function getAllPenalties()
    {
        $query = new AllPenalties($this->entityManager);

        return $query();
    }
    
    /**
     * @return Penalties[]
     */
    public function getAllCausal()
    {
        $query = new AllCausal($this->entityManager);

        return $query();
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\AllPenalties;
use SharengoCore\Entity\Queries\AllCausal;
use SharengoCore\Entity\Repository\PenaltiesRepository;

use Doctrine\ORM\EntityManager;

class PenaltiesService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;
    
    /**
     * @var PenaltiesRepository
     */
    private $penaltiesRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->penaltiesRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Penalty');
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
    
    /**
     * @return Penalties
     */
    public function findById($id){
         return $this->penaltiesRepository->findById($id)[0];
    }
}

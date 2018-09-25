<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\MaintenanceMotivationsRepository;

use Doctrine\ORM\EntityManager;

class MaintenanceMotivationsService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    private $maintenanceMotivationsRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager,
        MaintenanceMotivationsRepository $maintenanceMotivationsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->maintenanceMotivationsRepository = $maintenanceMotivationsRepository;
    }

    /**
     * @return Array[]
     */
    public function getAllMaintenanceMotivations()
    {
        //$maintenanceMotivations = $this->maintenanceMotivationsRepository->findAllActive();
        $maintenanceMotivations = $this->maintenanceMotivationsRepository->findAll();
        
        foreach ($maintenanceMotivations as $maintenanceMotivation){
            $ret[$maintenanceMotivation->getId()] = $maintenanceMotivation->getDescription();

        }
        return $ret;
    }


    public function getById($id)
    {
        return $this->maintenanceMotivationsRepository->findById($id);
    }
    
    public function findAllNotActive(){
        return $this->maintenanceMotivationsRepository->findAllNotActive();
    }
}

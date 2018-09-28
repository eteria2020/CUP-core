<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\MaintenanceLocationsRepository;

use Doctrine\ORM\EntityManager;

class MaintenanceLocationsService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    private $maintenanceLocationsRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager,
        MaintenanceLocationsRepository $maintenanceLocationsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->maintenanceLocationsRepository = $maintenanceLocationsRepository;
    }

    /**
     * @return Array[]
     */
    public function getAllMaintenanceLocations()
    {
        //$maintenanceLocations = $this->maintenanceLocationsRepository->findAllActive();
        $maintenanceLocations = $this->maintenanceLocationsRepository->findAll();
        foreach ($maintenanceLocations as $maintenanceLocation){
            $ret[$maintenanceLocation->getId()] = $maintenanceLocation->getLocation();
        }
        return $ret;
    }


    public function getById($id)
    {
        return $this->maintenanceLocationsRepository->findById($id);
    }
    
    public function findAllNotActive(){ 
        return $this->maintenanceLocationsRepository->findAllNotActive();
    }
}

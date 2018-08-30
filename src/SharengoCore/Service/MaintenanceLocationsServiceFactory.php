<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MaintenanceLocationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $maintenanceLocationsRepository = $entityManager->getRepository('\SharengoCore\Entity\MaintenanceLocations');

        return new MaintenanceLocationsService($entityManager, $maintenanceLocationsRepository);
    }
}

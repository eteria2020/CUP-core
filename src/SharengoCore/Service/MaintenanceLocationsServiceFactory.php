<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MaintenanceLocationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $maintenanceMotivationsRepository = $entityManager->getRepository('\SharengoCore\Entity\MaintenanceMotivations');

        return new MaintenanceMotivationsService($entityManager, $maintenanceMotivationsRepository);
    }
}

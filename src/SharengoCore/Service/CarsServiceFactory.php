<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $carsRepository = $entityManager->getRepository('\SharengoCore\Entity\Cars');
        $carsMaintenanceRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsMaintenance');
        $userService = $serviceLocator->get('zfcuser_auth_service');

        return new CarsService($entityManager, $carsRepository, $carsMaintenanceRepository, $datatableService, $userService);
    }
}
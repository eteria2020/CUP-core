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
        $updateCarRepository = $entityManager->getRepository('\SharengoCore\Entity\UpdateCars');
        $userService = $serviceLocator->get('zfcuser_auth_service');

        return new CarsService($entityManager, $carsRepository, $updateCarRepository, $datatableService, $userService);
    }
}
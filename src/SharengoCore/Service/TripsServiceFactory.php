<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        return new TripsService($tripRepository, $I_datatableService);
    }
}
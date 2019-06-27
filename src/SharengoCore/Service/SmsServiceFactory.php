<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SmsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $config = $serviceLocator->get('Config');
        $logger = $serviceLocator->get('SharengoCore\Service\SimpleLoggerService');
        $configurationsService = $serviceLocator->get('SharengoCore\Service\ConfigurationsService');
        $tripsService = $serviceLocator->get('SharengoCore\Service\TripsService');

        return new SmsService(
            $entityManager,
            $config,
            $logger,
            $configurationsService,
            $tripsService
        );
    }
}

<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ServerScriptsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {        
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        return new ServerScriptsService($entityManager);
    }
}

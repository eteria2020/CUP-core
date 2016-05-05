<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class SessionDatatableServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        $datatablesSessionNamespace = $serviceLocator->get('Configuration')['session']['datatablesNamespace'];

        // Creating Session Container
        $sessionContainer = new Container($datatablesSessionNamespace);

        return new SessionDatatableService($datatableService, $sessionContainer);
    }
}

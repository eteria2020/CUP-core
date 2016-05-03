<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class SessionDatatableServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        // Creating Session Container
        $sessionContainer = new Container('datatableFilters');

        return new SessionDatatableService($datatableService, $sessionContainer);
    }
}

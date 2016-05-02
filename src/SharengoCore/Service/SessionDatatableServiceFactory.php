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
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        // Creating Session Container
        $sessionContainer = new Container($this->params['datatableFilters']);

        return new SessionDatatableService($entityManager, $datatableService, $sessionContainer);
    }
}

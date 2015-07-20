<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Basic()
        );

        return new CardsService($entityManager, $I_datatableService);
    }
}

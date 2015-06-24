<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $userService = $serviceLocator->get('zfcuser_auth_service');

        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cards(
                $I_datatableService->getQueryBuilder(),
                'e'
            )
        );

        $cardsService = $serviceLocator->get('SharengoCore\Service\CardsService');

        return new CustomersService($entityManager, $userService, $I_datatableService, $cardsService);
    }
}

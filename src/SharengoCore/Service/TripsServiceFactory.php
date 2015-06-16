<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use SharengoCore\Service\DatatableQueryBuilders;

class TripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        // decorate the query builder with the needed decorators
        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cars(
                new DatatableQueryBuilders\Cards(
                    new DatatableQueryBuilders\Customers(
                        $I_datatableService->getQueryBuilder()
                    )
                )
            )
        );

        return new TripsService($tripRepository, $I_datatableService);
    }
}

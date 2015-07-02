<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReservationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $reservationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Reservations');

        // decorate the query builder with the needed decorators
        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cars(
                new DatatableQueryBuilders\Customers(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        $carsService = $serviceLocator->get('SharengoCore\Service\CarsService');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        return new ReservationsService($reservationsRepository, $I_datatableService, $carsService, $entityManager);
    }
}
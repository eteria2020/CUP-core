<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripPaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripPaymentsRepository = $entityManager->getRepository('\SharengoCore\Entity\TripPayments');
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Customers(
                new DatatableQueryBuilders\Trips(
                    new DatatableQueryBuilders\Basic()
                ),
                't'
            )
        );

        return new TripPaymentsService(
            $tripPaymentsRepository,
            $datatableService,
            $entityManager
        );
    }
}

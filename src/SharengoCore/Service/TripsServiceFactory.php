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
        $customerService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $I_urlHelper = $serviceLocator->get('viewhelpermanager')->get('url');

        // decorate the query builder with the needed decorators
        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\TripPayments(
                new DatatableQueryBuilders\Cars(
                    new DatatableQueryBuilders\Fleets(
                        new DatatableQueryBuilders\Cards(
                            new DatatableQueryBuilders\Customers(
                                new DatatableQueryBuilders\Basic()
                            ),
                            'cu'
                        )
                    )
                )
            )
        );

        $commandsService = $serviceLocator->get('SharengoCore\Service\CommandsService');

        return new TripsService(
            $tripRepository,
            $I_datatableService,
            $I_urlHelper,
            $customerService,
            $commandsService
        );
    }
}

<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class TripPaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripPaymentsRepository = $entityManager->getRepository('\SharengoCore\Entity\TripPayments');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Customers(
                new DatatableQueryBuilders\Trips(
                    new DatatableQueryBuilders\Basic()
                ),
                't'
            )
        );

        // Creating DataTable Filters Session Container
        $datatableFiltersSessionContainer = new Container('datatableFilters');

        return new TripPaymentsService(
            $tripPaymentsRepository,
            $datatableService,
            $entityManager,
            $datatableFiltersSessionContainer
        );
    }
}

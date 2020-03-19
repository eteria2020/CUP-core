<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FinesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $safoPenaltyRepository = $entityManager->getRepository('\SharengoCore\Entity\SafoPenalty');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');
        $fleetService = $serviceLocator->get('SharengoCore\Service\fleetService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Trips(
                new DatatableQueryBuilders\Cars(
                    new DatatableQueryBuilders\Fleets(
                        new DatatableQueryBuilders\Cards(
                            new DatatableQueryBuilders\Customers(
                                new DatatableQueryBuilders\ExtraPayments(
                                    new DatatableQueryBuilders\Basic()
                                )
                            ),
                            'cu'
                        )
                    )
                )
            )
        );

        return new FinesService(
            $safoPenaltyRepository,
            $datatableService,
            $entityManager,
            $fleetService
        );
    }
}

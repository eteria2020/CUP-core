<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Service\DatatableQueryBuilders;
// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');
        $datatableServiceNotPayed = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');
        $customerService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $urlHelper = $serviceLocator->get('viewhelpermanager')->get('url');

        // decorate the query builder with the needed decorators
        $datatableService->setQueryBuilder(
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

        $datatableServiceNotPayed->setQueryBuilder(
            new DatatableQueryBuilders\TripPaymentNotPayed(
                new DatatableQueryBuilders\Cards(
                    new DatatableQueryBuilders\Cars(
                        new DatatableQueryBuilders\Fleets(
                            new DatatableQueryBuilders\CustomersNotGold(
                                new DatatableQueryBuilders\Basic()
                            ),
                            'INNER'
                        ),
                        'INNER'
                    ),
                    'cu',
                    'INNER'
                )
            )
        );

        $commandsService = $serviceLocator->get('SharengoCore\Service\CommandsService');

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        return new TripsService(
            $tripRepository,
            $datatableService,
            $datatableServiceNotPayed,
            $urlHelper,
            $customerService,
            $commandsService,
            $translator
        );
    }
}

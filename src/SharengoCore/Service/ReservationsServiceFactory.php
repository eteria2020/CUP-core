<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReservationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        
        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $reservationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Reservations');

        // decorate the query builder with the needed decorators
        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cars(
                new DatatableQueryBuilders\Customers(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        return new ReservationsService(
            $reservationsRepository,
            $datatableService,
            $customersService,
            $entityManager,
            $translator
        );
    }
}

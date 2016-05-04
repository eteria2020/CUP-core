<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class CarsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $carsRepository = $entityManager->getRepository('\SharengoCore\Entity\Cars');
        $carsDamagesRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsDamages');
        $fleetsRepository = $entityManager->getRepository('\SharengoCore\Entity\Fleet');
        $carsMaintenanceRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsMaintenance');
        $userService = $serviceLocator->get('zfcuser_auth_service');
        $reservationsService = $serviceLocator->get('SharengoCore\Service\ReservationsService');

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\CarsInfo(
                new DatatableQueryBuilders\Fleets(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        // Creating DataTable Filters Session Container
        $datatableFiltersSessionContainer = new Container('datatableFilters');

        return new CarsService(
            $entityManager,
            $carsRepository,
            $carsMaintenanceRepository,
            $carsDamagesRepository,
            $fleetsRepository,
            $datatableService,
            $userService,
            $reservationsService,
            $translator,
            $datatableFiltersSessionContainer
        );
    }
}

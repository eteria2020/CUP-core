<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
        $reservationsService = $serviceLocator->get('SharengoCore\Service\ReservationsService');
        $maintenanceMotivationsService = $serviceLocator->get('SharengoCore\Service\MaintenanceMotivationsService');

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\CarsInfo(
                new DatatableQueryBuilders\Fleets(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        return new CarsService(
            $entityManager,
            $carsRepository,
            $carsMaintenanceRepository,
            $carsDamagesRepository,
            $fleetsRepository,
            $datatableService,
            $reservationsService,
            $translator,
            $maintenanceMotivationsService
        );
    }
}

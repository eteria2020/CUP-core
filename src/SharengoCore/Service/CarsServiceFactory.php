<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $carsRepository = $entityManager->getRepository('\SharengoCore\Entity\Cars');
        $carsDamagesRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsDamages');
        $carsInfoRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsInfo');
        $fleetsRepository = $entityManager->getRepository('\SharengoCore\Entity\Fleet');
        $carsMaintenanceRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsMaintenance');
        $userService = $serviceLocator->get('zfcuser_auth_service');
        $reservationsService = $serviceLocator->get('SharengoCore\Service\ReservationsService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\CarsInfo(
	            new DatatableQueryBuilders\Fleets(
    	            new DatatableQueryBuilders\Basic()
				)
			)
        );

        return new CarsService($entityManager, 
                               $carsRepository,
                               $carsMaintenanceRepository,
                               $carsInfoRepository,
                               $carsDamagesRepository,
                               $fleetsRepository,
                               $datatableService,
                               $userService,
                               $reservationsService);
    }
}
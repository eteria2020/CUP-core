<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsConfigurationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        $carsConfigurationsRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsConfigurations');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cars(
                new DatatableQueryBuilders\Fleets(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        return new CarsConfigurationsService(
            $entityManager,
            $carsConfigurationsRepository,
            $datatableService
        );
    }
}

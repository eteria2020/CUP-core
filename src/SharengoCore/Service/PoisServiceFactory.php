<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PoisServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        /* @var $datatableService SharengoCore\Service\DatatableService */
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $poisRepository = $entityManager->getRepository('\SharengoCore\Entity\Pois');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Pois(
                new DatatableQueryBuilders\Basic()
            )
        );

        return new PoisService(
            $entityManager,
            $poisRepository,
            $datatableService
        );
    }
}

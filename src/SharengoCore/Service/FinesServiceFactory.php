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

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\SafoPenalty(
                new DatatableQueryBuilders\Basic()
            )
        );

        $faresService = $serviceLocator->get('SharengoCore\Service\FaresService');

        return new FinesService(
            $safoPenaltyRepository,
            $datatableService,
            $entityManager,
            $faresService
        );
    }
}
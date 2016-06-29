<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotificationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $notificationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Notifications');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Basic()
        );

        return new NotificationsService(
            $entityManager,
            $notificationsRepository,
            $datatableService
        );
    }
}

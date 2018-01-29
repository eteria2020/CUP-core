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
        $userService = $serviceLocator->get('zfcuser_auth_service');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');
        $customerService = $serviceLocator->get('SharengoCore\Service\CustomersService');

        $notificationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Notifications');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\NotificationsProtocols(
                new DatatableQueryBuilders\NotificationsCategories(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        return new NotificationsService(
            $entityManager,
            $notificationsRepository,
            $datatableService,
            $customerService,
            $userService
        );
    }
}

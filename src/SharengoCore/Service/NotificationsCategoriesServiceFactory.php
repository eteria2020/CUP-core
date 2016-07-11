<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotificationsCategoriesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $notificationsCategoriesRepository = $entityManager->getRepository('\SharengoCore\Entity\NotificationsCategories');

        return new NotificationsCategoriesService(
            $notificationsCategoriesRepository
        );
    }
}

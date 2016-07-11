<?php

namespace SharengoCore\Service\NotificationsCategories;

// Internals
use SharengoCore\Service\NotificationsCategories\NotificationsCategoriesAbstractFactory;
// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotificationsCategoriesAbstractFactoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NotificationsCategoriesAbstractFactory($serviceLocator);
    }
}

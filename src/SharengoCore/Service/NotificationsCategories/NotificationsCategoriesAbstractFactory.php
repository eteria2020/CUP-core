<?php

namespace SharengoCore\Service\NotificationsCategories;

// Internals
use SharengoCore\Exception\NotificationsServiceNotFoundException;
// Externals
use Zend\ServiceManager\ServiceLocatorInterface;
use ReflectionClass;

class NotificationsCategoriesAbstractFactory
{
    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct(
        ServiceLocatorInterface $serviceLocator
    ) {
        $this->serviceLocator = $serviceLocator;
    }

    public function createServiceWithName($name) {
        // Create a ReflectionClass to check if the $name implements the required interface.
        $reflectionClass = new ReflectionClass($name);

        if (!class_exists($name) || !$reflectionClass->implementsInterface("SharengoCore\Service\NotificationsCategories\NotificationsCategoriesInterface")) {
            throw new NotificationsServiceNotFoundException;
        }

        return $this->serviceLocator->get($name);
    }
}
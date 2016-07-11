<?php

namespace SharengoCore\Service\NotificationsCategories;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SOSCategoryServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return SOSCategoryService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $tripService = $serviceLocator->get('SharengoCore\Service\TripsService');

        return new SOSCategoryService(
            $customersService,
            $tripService
        );
    }
}

<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidateForeignDriversLicenseServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $customerDeactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');

        return new ValidateForeignDriversLicenseService(
            $entityManager,
            $customerDeactivationService
        );
    }
}

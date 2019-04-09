<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DriversLicenseValidationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $repository = $entityManager->getRepository('\SharengoCore\Entity\DriversLicenseValidation');
        $countriesService = $serviceLocator->get('SharengoCore\Service\CountriesService');

        return new DriversLicenseValidationService($entityManager, $repository, $countriesService);
    }
}

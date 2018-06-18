<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PartnerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $customersRepository = $entityManager->getRepository('\SharengoCore\Entity\Customers');
        $countriesService = $serviceLocator->get('SharengoCore\Service\CountriesService');
        $driversLicenseValidationService = $serviceLocator->get('SharengoCore\Service\DriversLicenseValidationService');
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');
        $fleetService = $serviceLocator->get('SharengoCore\Service\FleetService');
        $telepassService = $serviceLocator->get('SharengoCore\Service\Partner\TelepassService');
        $nugoService = $serviceLocator->get('SharengoCore\Service\Partner\NugoService');

        return new PartnerService(
            $entityManager,
            $customersRepository,
            $countriesService,
            $driversLicenseValidationService,
            $partnersRepository,
            $fleetService,
            $telepassService,
            $nugoService
        );
    }
}

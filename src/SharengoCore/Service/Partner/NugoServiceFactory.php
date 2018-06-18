<?php

namespace SharengoCore\Service\Partner;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class NugoServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $events = $serviceLocator->get('EventManager');
        $events->addIdentifiers('Application\Service\RegistrationService');

        $customersRepository = $entityManager->getRepository('\SharengoCore\Entity\Customers');
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');
        $provincesRepository = $entityManager->getRepository('\SharengoCore\Entity\Provinces');

        $fleetService = $serviceLocator->get('SharengoCore\Service\FleetService');
        $userEventsService = $serviceLocator->get('SharengoCore\Service\UserEventsService');
        $driversLicenseValidationService = $serviceLocator->get('SharengoCore\Service\DriversLicenseValidationService');
        $countriesService = $serviceLocator->get('SharengoCore\Service\CountriesService');

        return new NugoService(
            $events,
            $entityManager,
            $customersRepository,
            $partnersRepository,
            $fleetService,
            $provincesRepository,
            $userEventsService,
            $driversLicenseValidationService,
            $countriesService
        );
    }
}

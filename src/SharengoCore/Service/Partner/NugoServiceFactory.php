<?php

namespace SharengoCore\Service\Partner;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class NugoServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $customersRepository = $entityManager->getRepository('\SharengoCore\Entity\Customers');
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');
        $provincesRepository = $entityManager->getRepository('\SharengoCore\Entity\Provinces');

        $fleetService = $serviceLocator->get('SharengoCore\Service\FleetService');
        $userEventsService = $serviceLocator->get('SharengoCore\Service\UserEventsService');
        $driversLicenseValidationService = $serviceLocator->get('SharengoCore\Service\DriversLicenseValidationService');

        return new NugoService(
            $entityManager,
            $customersRepository,
            $partnersRepository,
            $fleetService,
            $provincesRepository,
            $userEventsService,
            $driversLicenseValidationService
        );
    }
}

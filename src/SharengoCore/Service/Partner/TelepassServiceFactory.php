<?php

namespace SharengoCore\Service\Partner;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TelepassServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $customersRepository = $entityManager->getRepository('\SharengoCore\Entity\Customers');
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');
        $provincesRepository = $entityManager->getRepository('\SharengoCore\Entity\Provinces');
        $tripsRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');

        $logger = $serviceLocator->get('SharengoCore\Service\SimpleLoggerService');
        $config = $serviceLocator->get('Config');
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $deactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');
        $fleetService = $serviceLocator->get('SharengoCore\Service\FleetService');
        $userEventsService = $serviceLocator->get('SharengoCore\Service\UserEventsService');
        $countriesService = $serviceLocator->get('SharengoCore\Service\CountriesService');
        $invoicesService  = $serviceLocator->get('SharengoCore\Service\Invoices');

        $driversLicenseValidationService = $serviceLocator->get('SharengoCore\Service\DriversLicenseValidationService');
        $portaleAutomobilistaValidationService = $serviceLocator->get('MvLabsDriversLicenseValidation\PortaleAutomobilista');

        return new TelepassService(
            $entityManager,
            $logger,
            $config,
            $customersRepository,
            $partnersRepository,
            $tripsRepository,
            $customersService,
            $deactivationService,
            $fleetService,
            $provincesRepository,
            $userEventsService,
            $countriesService,
            $invoicesService,
            $driversLicenseValidationService,
            $portaleAutomobilistaValidationService
        );
    }
}

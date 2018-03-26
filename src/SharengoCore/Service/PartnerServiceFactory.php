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
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');
        $fleetService = $serviceLocator->get('SharengoCore\Service\FleetService');
        $telepassService = $serviceLocator->get('SharengoCore\Service\TelepassService');

        return new PartnerService(
            $entityManager,
            $customersRepository,
            $partnersRepository,
            $fleetService,
            $telepassService
        );
    }
}

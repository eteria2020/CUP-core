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
        $fleetService = $serviceLocator->get('SharengoCore\Service\FleetService');

        return new PartnerService(
            $entityManager,
            $customersRepository,
            $fleetService
        );
    }
}

<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProviderAuthenticatedCustomersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $repository = $entityManager->getRepository('\SharengoCore\Entity\ProviderAuthenticatedCustomer');

        return new ProviderAuthenticatedCustomersService(
            $entityManager,
            $repository
        );
    }
}

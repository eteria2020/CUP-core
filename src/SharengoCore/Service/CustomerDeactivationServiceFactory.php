<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomerDeactivationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $customerService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $repository = $entityManager->getRepository('SharengoCore\Entity\CustomerDeactivation');

        return new CustomerDeactivationService($entityManager, $customerService, $repository);
    }
}

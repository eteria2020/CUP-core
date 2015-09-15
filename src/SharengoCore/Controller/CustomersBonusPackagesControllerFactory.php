<?php

namespace SharengoCore\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CustomersBonusPackagesControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $customersBonusPackagesService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\BonusPackagesService');
        $entityManager = $serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $hydrator = new DoctrineHydrator($entityManager);

        return new CustomersBonusPackagesController($customersBonusPackagesService, $hydrator);
    }
}

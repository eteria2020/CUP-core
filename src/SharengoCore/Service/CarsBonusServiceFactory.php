<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsBonusServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $carsBonusRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsBonus');

        return new CarsBonusService(
            $entityManager,
            $carsBonusRepository
        );
    }
}

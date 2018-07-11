<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsBonusHistoryServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $carsBonusHistyìoryRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsBonusHistory');

        return new CarsBonusService(
            $entityManager,
            $carsBonusHistyìoryRepository
        );
    }
}

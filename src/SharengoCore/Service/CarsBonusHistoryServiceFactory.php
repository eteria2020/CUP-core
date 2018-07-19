<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsBonusHistoryServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $carsBonusHistyoryRepository = $entityManager->getRepository('\SharengoCore\Entity\CarsBonusHistory');

        return new CarsBonusHistoryService(
            $entityManager,
            $carsBonusHistyoryRepository
        );
    }
}

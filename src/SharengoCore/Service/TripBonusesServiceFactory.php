<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripBonusesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripBonusesRepository = $entityManager->getRepository('\SharengoCore\Entity\TripBonuses');

        return new TripBonusesService(
            $tripBonusesRepository
        );
    }
}

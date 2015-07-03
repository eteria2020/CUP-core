<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AccountTripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $bonusRepository = $entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $freeFaresRepository = $entityManager->getRepository('\SharengoCore\Entity\FreeFares');
        $bonusService = $serviceLocator->get('SharengoCore\Service\BonusService');
        $freeFaresService = $serviceLocator->get('SharengoCore\Service\FreeFaresService');

        return new AccountTripsService(
            $entityManager,
            $bonusRepository,
            $freeFaresRepository,
            $bonusService,
            $freeFaresService
        );
    }
}

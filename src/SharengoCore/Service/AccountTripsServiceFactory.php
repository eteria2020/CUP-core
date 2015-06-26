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
        $bonusService = $serviceLocator->get('SharengoCore\Service\BonusService');

        return new AccountTripsService(
            $entityManager,
            $bonusRepository,
            $bonusService
        );
    }
}

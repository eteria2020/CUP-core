<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class DisableContractServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $cartasiContractsService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $tripPaymentsService = $serviceLocator->get('SharengoCore\Service\TripPaymentsService');
        $eventManager = new EventManager('DisableContractService');

        return new DisableContractService(
            $entityManager,
            $cartasiContractsService,
            $tripPaymentsService,
            $eventManager
        );
    }
}

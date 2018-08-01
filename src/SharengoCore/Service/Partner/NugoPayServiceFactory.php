<?php

namespace SharengoCore\Service\Partner;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class NugoPayServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $eventManager = new EventManager('NugoPayService');

        $tripsService = $serviceLocator->get('SharengoCore\Service\TripsService');
        $extraPaymentsService = $serviceLocator->get('SharengoCore\Service\ExtraPaymentsService');
        $cartasiContractsService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');

        return new NugoPayService(
            $entityManager,
            $eventManager,
            $tripsService,
            $extraPaymentsService,
            $cartasiContractsService,
            $partnersRepository
        );
    }
}

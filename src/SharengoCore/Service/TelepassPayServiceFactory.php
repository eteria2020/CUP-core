<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TelepassPayServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $telepassPayConfig = $config['telepassPay'];
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $tripsService = $serviceLocator->get('SharengoCore\Service\TripsService');
        $extraPaymentsService = $serviceLocator->get('SharengoCore\Service\ExtraPaymentsService');
        $cartasiContractsService = $serviceLocator->get('Cartasi\Service\CartasiContracts');

        return new TelepassPayService(
            $telepassPayConfig,
            $entityManager,
            $tripsService,
            $extraPaymentsService,
            $cartasiContractsService
        );
    }
}

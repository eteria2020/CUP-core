<?php

namespace SharengoCore\Service\Partner;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TelepassPayServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');

        $tripsService = $serviceLocator->get('SharengoCore\Service\TripsService');
        $extraPaymentsService = $serviceLocator->get('SharengoCore\Service\ExtraPaymentsService');
        $cartasiContractsService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $partnersRepository = $entityManager->getRepository('\SharengoCore\Entity\Partners');

        return new TelepassPayService(
            $entityManager,
            $tripsService,
            $extraPaymentsService,
            $cartasiContractsService,
            $partnersRepository
        );
    }
}

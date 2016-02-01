<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class PaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartasiCustomerPayments = $serviceLocator->get('Cartasi\Service\CartasiCustomerPaymentsRetry');
        $cartasiContractService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');
        $tripPaymentTriesService = $serviceLocator->get('SharengoCore\Service\TripPaymentTriesService');
        $eventManager = new EventManager();
        $eventManager->setIdentifiers(['PaymentsService']);
        $url = $serviceLocator->get('Configuration')['website']['uri'];
        $deactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');

        return new PaymentsService(
            $cartasiCustomerPayments,
            $cartasiContractService,
            $entityManager,
            $emailService,
            $eventManager,
            $tripPaymentTriesService,
            $url,
            $deactivationService
        );
    }
}

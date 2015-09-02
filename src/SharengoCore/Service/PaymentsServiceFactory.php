<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartasiCustomerPayments = $serviceLocator->get('Cartasi\Service\CartasiCustomerPayments');
        $cartasiContractService = $serviceLocator->get('CartasiContractsService $cartasiContractService');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');

        return new PaymentsService(
            $cartasiCustomerPayments,
            $cartasiContractService,
            $entityManager,
            $emailService
        );
    }
}

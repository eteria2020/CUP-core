<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BuyCustomerBonusPackageFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $payments = $serviceLocator->get('Cartasi\Service\CartasiCustomerPayments');
        $customersPointsService = $serviceLocator->get('SharengoCore\Service\CustomersPointsService');
        $cartasiContractService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $gpwebpayCustomerPayments = $serviceLocator->get('GPWebpay\Service\GPWebpayCustomerPayments');
        $mollieCustomerPayments = $serviceLocator->get('Mollie\Service\MollieCustomerPayments');
        $bankartCustomerPayments = $serviceLocator->get('Bankart\Service\BankartCustomerPayments');

        return new BuyCustomerBonusPackage(
            $entityManager,
            $payments,
            $customersPointsService,
            $cartasiContractService,
            $gpwebpayCustomerPayments,
            $mollieCustomerPayments,
            $bankartCustomerPayments
        );
    }
}

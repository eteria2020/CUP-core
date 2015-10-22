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

        return new BuyCustomerBonusPackage($entityManager, $payments);
    }
}

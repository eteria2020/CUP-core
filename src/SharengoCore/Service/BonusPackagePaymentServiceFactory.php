<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BonusPackagePaymentServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $invoicesService = $serviceLocator->get('SharengoCore\Service\Invoices');

        return new BonusPackagePaymentService($entityManager, $invoicesService);
    }
}

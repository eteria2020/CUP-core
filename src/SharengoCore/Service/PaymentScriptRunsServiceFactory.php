<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PaymentScriptRunsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $connection = $serviceLocator->get('doctrine.connection.orm_default');

        return new PaymentScriptRunsService($connection);
    }
}

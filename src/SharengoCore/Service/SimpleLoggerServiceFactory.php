<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SimpleLoggerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $simpleLoggerConfig = $config['simple-logger'];

        return new SimpleLoggerService($simpleLoggerConfig);
    }
}

<?php

namespace SharengoCore\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PoisControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->getServiceLocator()->get('Config');
        $apiUrl = $config['api']['url'] . '/pois';

        $poisService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\PoisService');
        
        return new PoisController($apiUrl, $poisService);
    }
}

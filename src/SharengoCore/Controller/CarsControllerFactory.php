<?php

namespace SharengoCore\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->getServiceLocator()->get('Config');
        $apiUrl = $config['api']['url'] . '/cars';

        $carsService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\CarsService');

        return new CarsController($apiUrl, $carsService);
    }
}

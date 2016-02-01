<?php

namespace SharengoCore\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MunicipalitiesControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $municipalitiesService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\MunicipalitiesService');

        return new MunicipalitiesController($municipalitiesService);
    }
}

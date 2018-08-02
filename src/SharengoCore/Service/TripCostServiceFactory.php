<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripCostServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $faresService = $serviceLocator->get('SharengoCore\Service\FaresService');
        $tripFaresService = $serviceLocator->get('SharengoCore\Service\TripFaresService');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $preauthorizationsService = $serviceLocator->get('SharengoCore\Service\PreauthorizationsService');

        /* 
         * Be carefull: 
         * if you change the signature of TripCostService(),
         * remember to update TripCostComputerServiceFactory(), row 34
         */
        return new TripCostService(
            $faresService,
            $tripFaresService,
            $entityManager,
            $preauthorizationsService
        );
    }
}

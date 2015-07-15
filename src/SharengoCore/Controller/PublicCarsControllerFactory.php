<?php

namespace SharengoCore\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class PublicCarsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $carsService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\CarsService');
        $reservationsService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\ReservationsService');
        $tripsService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\TripsService');
        $authService = $serviceLocator->getServiceLocator()->get('zfcuser_auth_service');
        $entityManager = $serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $hydrator = new DoctrineHydrator($entityManager);

        return new PublicCarsController($carsService, $reservationsService, $tripsService, $authService, $hydrator);
    }
}

<?php

namespace SharengoCore\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class TripsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $tripsService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\TripsService');
        $tripPaymentsService = $serviceLocator->getServiceLocator()->get('SharengoCore\Service\TripPaymentsService');
        $entityManager = $serviceLocator->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $authService = $serviceLocator->getServiceLocator()->get('zfcuser_auth_service');
        $hydrator = new DoctrineHydrator($entityManager);

        return new TripsController($tripsService, $tripPaymentsService, $hydrator, $authService);
    }
}

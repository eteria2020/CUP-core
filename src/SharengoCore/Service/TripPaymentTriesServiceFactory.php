<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripPaymentTriesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripPaymentTriesRepository = $entityManager->getRepository('\SharengoCore\Entity\TripPaymentTries');

        return new TripPaymentTriesService($entityManager, $tripPaymentTriesRepository);
    }
}

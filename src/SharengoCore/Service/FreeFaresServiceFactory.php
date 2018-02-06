<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FreeFaresServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripsRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $reservationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Reservations');
        $eventsRepository = $entityManager->getRepository('\SharengoCore\Document\Events');

        return new FreeFaresService($tripsRepository, $reservationsRepository, $entityManager, $eventsRepository);
    }
}
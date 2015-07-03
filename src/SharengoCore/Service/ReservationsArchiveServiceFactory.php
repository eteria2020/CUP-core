<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReservationsArchiveServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $reservationsArchiveRepository = $entityManager->getRepository('\SharengoCore\Entity\ReservationsArchive');

        return new ReservationsArchiveService($reservationsArchiveRepository);
    }
}

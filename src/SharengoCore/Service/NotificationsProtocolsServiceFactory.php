<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotificationsProtocolsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $notificationsProtocolsRepository = $entityManager->getRepository('\SharengoCore\Entity\NotificationsProtocols');

        return new NotificationsProtocolsService(
            $notificationsProtocolsRepository
        );
    }
}

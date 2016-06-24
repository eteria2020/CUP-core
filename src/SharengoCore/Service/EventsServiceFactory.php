<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $documentManager = $serviceLocator->get('doctrine.documentmanager.odm_default');
        $repository = $documentManager->getRepository('\SharengoCore\Document\Events');
        $eventsTypesService = $serviceLocator->get('SharengoCore\Service\EventsTypesService');

        return new EventsService($repository, $eventsTypesService);
    }
}

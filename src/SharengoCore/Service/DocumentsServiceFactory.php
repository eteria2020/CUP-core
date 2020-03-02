<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class DocumentsServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $documentsRepository = $entityManager->getRepository('\SharengoCore\Entity\Documents');

        return new DocumentsService(
            $entityManager,
            $documentsRepository
        );
    }

}

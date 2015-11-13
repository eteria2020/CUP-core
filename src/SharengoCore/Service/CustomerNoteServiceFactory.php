<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomerNoteServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $repository = $entityManager->getRepository('\SharengoCore\Entity\CustomerNote');

        return new CustomerNoteService($repository, $entityManager);
    }
}

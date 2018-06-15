<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MunicipalitiesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $municipalityRepository = $entityManager->getRepository('\SharengoCore\Entity\Municipality');

        return new MunicipalitiesService($entityManager, $municipalityRepository);
    }
}

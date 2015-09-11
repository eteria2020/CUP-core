<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripFreeFaresServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripFreeFaresRepository = $entityManager->getRepository('\SharengoCore\Entity\TripFreeFares');

        return new TripFreeFaresService(
            $tripFreeFaresRepository
        );
    }
}

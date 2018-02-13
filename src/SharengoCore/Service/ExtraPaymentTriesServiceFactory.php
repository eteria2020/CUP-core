<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExtraPaymentTriesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $extraPaymentTriesRepository = $entityManager->getRepository('\SharengoCore\Entity\ExtraPaymentTries');

        return new ExtraPaymentTriesService($entityManager, $extraPaymentTriesRepository);
    }
}

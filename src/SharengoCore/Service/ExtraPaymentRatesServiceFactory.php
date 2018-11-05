<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExtraPaymentRatesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $extraPaymentRatesRepository = $entityManager->getRepository('\SharengoCore\Entity\ExtraPaymentRates');

        return new ExtraPaymentRatesService($entityManager, $extraPaymentRatesRepository);
    }
}

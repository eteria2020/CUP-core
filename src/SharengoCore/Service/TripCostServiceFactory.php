<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripCostServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $faresService = $serviceLocator->get('SharengoCore\Service\FaresService');
        $tripFaresService = $serviceLocator->get('SharengoCore\Service\TripFaresService');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');
        $cartasiCustomerPayments = $serviceLocator->get('Cartasi\Service\CartasiCustomerPayments');

        return new TripCostService(
            $faresService,
            $tripFaresService,
            $entityManager,
            $emailService,
            $cartasiCustomerPayments
        );
    }
}

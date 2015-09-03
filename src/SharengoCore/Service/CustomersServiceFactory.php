<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userService = $serviceLocator->get('zfcuser_auth_service');
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $cardsService = $serviceLocator->get('SharengoCore\Service\CardsService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cards(
                new DatatableQueryBuilders\Basic(),
                'e'
            )
        );

        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');
        $logger = $serviceLocator->get('SharengoCore\Service\SimpleLoggerService');
        $cartasiContractsService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $tripPaymentsService = $serviceLocator->get('SharengoCore\Service\TripPaymentsService');
        $url = $serviceLocator->get('Configuration')['website']['uri'];

        return new CustomersService(
            $entityManager,
            $userService,
            $datatableService,
            $cardsService,
            $emailService,
            $logger,
            $cartasiContractsService,
            $tripPaymentsService,
            $url
        );
    }
}

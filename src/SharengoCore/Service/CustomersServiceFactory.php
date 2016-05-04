<?php

namespace SharengoCore\Service;

// Externals
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userService = $serviceLocator->get('zfcuser_auth_service');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

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

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        return new CustomersService(
            $entityManager,
            $userService,
            $datatableService,
            $cardsService,
            $emailService,
            $translator,
            $logger,
            $cartasiContractsService,
            $tripPaymentsService,
            $url
        );
    }
}

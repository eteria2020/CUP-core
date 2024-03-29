<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Customers(
                new DatatableQueryBuilders\Basic(),
                'e'
            )
        );

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        return new CardsService($entityManager, $I_datatableService, $translator);
    }
}

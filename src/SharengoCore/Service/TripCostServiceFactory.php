<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Client;

class TripCostServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $faresService = $serviceLocator->get('SharengoCore\Service\FaresService');
        $tripFaresService = $serviceLocator->get('SharengoCore\Service\TripFaresService');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $httpClient = new Client();
        $url = $serviceLocator->get('ViewHelperManager')->get('Url');
        $cartasiContractsService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $cartasiTransactionsRepository = $entityManager->getRepository('\Cartasi\Entity\Transactions');
        $config = $serviceLocator->get('Config');
        $websiteConfig = $config['website'];
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');

        return new TripCostService(
            $faresService,
            $tripFaresService,
            $entityManager,
            $httpClient,
            $url,
            $cartasiContractsService,
            $cartasiTransactionsRepository,
            $websiteConfig,
            $emailService
        );
    }
}

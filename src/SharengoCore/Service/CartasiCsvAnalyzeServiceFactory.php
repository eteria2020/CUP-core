<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CartasiCsvAnalyzeServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $cartasiCsvFileRepository = $entityManager->getRepository('\SharengoCore\Entity\CartasiCsvFile');
        $cartasiCsvAnomalyRepository = $entityManager->getRepository('\SharengoCore\Entity\CartasiCsvAnomaly');
        $cartasiCsvService = $serviceLocator->get('Cartasi\Service\CartasiCsv');
        $cartasiPaymentsService = $serviceLocator->get('Cartasi\Service\CartasiPayments');
        $config = $serviceLocator->get('Config');
        $csvConfig = $config['csv'];

        return new CartasiCsvAnalyzeService(
            $entityManager,
            $cartasiCsvFileRepository,
            $cartasiCsvAnomalyRepository,
            $cartasiCsvService,
            $cartasiPaymentsService,
            $csvConfig
        );
    }
}

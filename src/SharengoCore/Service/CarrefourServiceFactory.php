<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CarrefourServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $repository = $entityManager->getRepository('\SharengoCore\Entity\CarrefourUsedCode');
        $config = $serviceLocator->get('Config');
        $pcConfig = $config['carrefour'];
        $pcMarketConfig = $config['carrefourMarket'];

        return new CarrefourService(
            $entityManager,
            $repository,
            $pcConfig,
            $pcMarketConfig
        );
    }
}

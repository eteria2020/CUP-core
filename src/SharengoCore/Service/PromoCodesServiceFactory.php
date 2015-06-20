<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PromoCodesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $pcRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodes');
        $pcInfoRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodesInfo');

        return new PromoCodesService($pcRepository, $pcInfoRepository);
    }
}

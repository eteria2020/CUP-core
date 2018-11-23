<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PromoCodesACIServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $pcRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodes');
        $pcInfoRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodesInfo');
        $customerBonusRepository = $entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $pcService = $serviceLocator->get('SharengoCore\Service\PromoCodesService');
        $config = $serviceLocator->get('Config');

        return new PromoCodesACIService(
            $entityManager,
            $customersService,
            $pcRepository,
            $pcInfoRepository,
            $pcService,
            $config['aci'],
            $customerBonusRepository
        );
    }
}

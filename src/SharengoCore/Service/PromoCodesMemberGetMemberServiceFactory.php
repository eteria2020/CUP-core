<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PromoCodesMemberGetMemberServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $pcRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodes');
        $pcInfoRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodesInfo');
        $pcOnceRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodesOnce');
        $pcService = $serviceLocator->get('SharengoCore\Service\PromoCodesService');

        return new PromoCodesMemberGetMemberService(
            $entityManager,
            $customersService,
            $pcRepository,
            $pcInfoRepository,
            $pcOnceRepository,
            $pcService
        );
    }
}

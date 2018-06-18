<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PromoCodesOnceServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');

        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $customersRepository = $entityManager->getRepository('\SharengoCore\Entity\Customers');
        $pcRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodes');
        $pcoRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodesOnce');
        $pcInfoRepository = $entityManager->getRepository('\SharengoCore\Entity\PromoCodesInfo');
        $logger = $serviceLocator->get('SharengoCore\Service\SimpleLoggerService');

        return new PromoCodesOnceService(
            $customersService,
            $entityManager,
            $customersRepository,
            $pcRepository,
            $pcoRepository,
            $pcInfoRepository,
            $logger
        );
    }
}

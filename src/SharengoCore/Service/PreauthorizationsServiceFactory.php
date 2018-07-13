<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PreauthorizationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $preauthorizationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Preauthorizations');
        $cartasiCustomerPayments = $serviceLocator->get('Cartasi\Service\CartasiCustomerPaymentsRetry');
        $preauthorizationsAmount = $serviceLocator->get('Configuration')['cartasi']['preauthorization_amount'];
        return new PreauthorizationsService(
            $entityManager,
            $preauthorizationsRepository,
            $cartasiCustomerPayments,
            $preauthorizationsAmount
        );
    }
}

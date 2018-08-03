<?php

namespace SharengoCore\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotifyPartnerCustomerStatusListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $partnerService = $serviceLocator->get('SharengoCore\Service\PartnerService');

        return new NotifyPartnerCustomerStatusListener($partnerService);
    }
}

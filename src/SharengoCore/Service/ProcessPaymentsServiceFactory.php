<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class ProcessPaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $eventManager = new EventManager('ProcessPaymentsService');
        $logger = $serviceLocator->get('SharengoCore\Service\BlackHoleLogger');
        $paymentEmailListener = $serviceLocator->get('SharengoCore\Listener\PaymentEmailListener');
        $notifyCustomerPayListener = $serviceLocator->get('SharengoCore\Listener\NotifyCustomerPayListener');
        $paymentsService = $serviceLocator->get('SharengoCore\Service\PaymentsService');
        $tripPaymentsService = $serviceLocator->get('SharengoCore\Service\TripPaymentsService');
        $customerDeactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');

        return new ProcessPaymentsService(
            $eventManager,
            $logger,
            $paymentEmailListener,
            $notifyCustomerPayListener,
            $paymentsService,
            $tripPaymentsService,
            $customerDeactivationService
        );
    }
}

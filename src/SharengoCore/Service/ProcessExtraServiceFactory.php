<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class ProcessExtraServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $eventManager = new EventManager('ProcessPaymentsService');
        $logger = $serviceLocator->get('SharengoCore\Service\BlackHoleLogger');
        $paymentEmailListener = $serviceLocator->get('SharengoCore\Listener\PaymentEmailListener');
        $notifyCustomerPayListener = $serviceLocator->get('SharengoCore\Listener\NotifyCustomerPayListener');
        $paymentsService = $serviceLocator->get('SharengoCore\Service\PaymentsService');
        $tripPaymentsService = $serviceLocator->get('SharengoCore\Service\TripPaymentsService');
        $extraPaymentsService = $serviceLocator->get('SharengoCore\Service\ExtraPaymentsService');
        $customerDeactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');

        $usersService = $serviceLocator->get('SharengoCore\Service\UsersService');

        return new ProcessExtraService(
            $eventManager,
            $logger,
            $paymentEmailListener,
            $notifyCustomerPayListener,
            $paymentsService,
            $tripPaymentsService,
            $extraPaymentsService,
            $customerDeactivationService,
            $usersService,
            $customersService
        );
    }
}

<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class PaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartasiCustomerPayments = $serviceLocator->get('Cartasi\Service\CartasiCustomerPaymentsRetry');
        $cartasiContractService = $serviceLocator->get('Cartasi\Service\CartasiContracts');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');
        $tripPaymentTriesService = $serviceLocator->get('SharengoCore\Service\TripPaymentTriesService');
        $extraPaymentTriesService = $serviceLocator->get('SharengoCore\Service\ExtraPaymentTriesService');

        $eventManager = new EventManager('PaymentsService');
        $paymentEmailListener = $serviceLocator->get('SharengoCore\Listener\PaymentEmailListener');
        $eventManager->getSharedManager()->attachAggregate($paymentEmailListener);
//        $eventManager->setIdentifiers(['PaymentsService']);

        $url = $serviceLocator->get('Configuration')['website']['uri'];
        $preauthorizationsAmount = $serviceLocator->get('Configuration')['cartasi']['preauthorization_amount'];
        $deactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');
        $preauthorizationsService = $serviceLocator->get('SharengoCore\Service\PreauthorizationsService');
        $freeFaresRepository = $entityManager->getRepository('\SharengoCore\Entity\FreeFares');
        $tripsRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $reservationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Reservations');
        $telepassPayService = $serviceLocator->get('SharengoCore\Service\Partner\TelepassPayService');
        $nugoPayService = $serviceLocator->get('SharengoCore\Service\Partner\NugoPayService');
        $gpwebpayCustomerPayments = $serviceLocator->get('GPWebpay\Service\GPWebpayCustomerPayments');
        $mollieCustomerPayments = $serviceLocator->get('Mollie\Service\MollieCustomerPayments');
        $bankartCustomerPayments = $serviceLocator->get('Bankart\Service\BankartCustomerPayments');

        return new PaymentsService(
            $cartasiCustomerPayments,
            $cartasiContractService,
            $entityManager,
            $emailService,
            $eventManager,
            $tripPaymentTriesService,
            $extraPaymentTriesService,
            $url,
            $deactivationService,
            $preauthorizationsService,
            $preauthorizationsAmount,
            $freeFaresRepository,
            $tripsRepository,
            $reservationsRepository,
            $telepassPayService,
            $nugoPayService,
            $gpwebpayCustomerPayments,
            $mollieCustomerPayments,
            $bankartCustomerPayments
        );
    }
}

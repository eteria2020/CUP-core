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
        $eventManager = new EventManager();
        $eventManager->setIdentifiers(['PaymentsService']);
        $url = $serviceLocator->get('Configuration')['website']['uri'];
        $preauthorizationsAmount = $serviceLocator->get('Configuration')['cartasi']['preauthorization_amount'];
        $deactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');
        $preauthorizationsService = $serviceLocator->get('SharengoCore\Service\PreauthorizationsService');
        $freeFaresRepository = $entityManager->getRepository('\SharengoCore\Entity\FreeFares');
        $tripsRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $reservationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Reservations');
        $telepassPayService = $serviceLocator->get('SharengoCore\Service\TelepassPayService');

        return new PaymentsService(
            $cartasiCustomerPayments,
            $cartasiContractService,
            $entityManager,
            $emailService,
            $eventManager,
            $tripPaymentTriesService,
            $url,
            $deactivationService,
            $preauthorizationsService,
            $preauthorizationsAmount,
            $freeFaresRepository,
            $tripsRepository,
            $reservationsRepository,
            $telepassPayService
        );
    }
}

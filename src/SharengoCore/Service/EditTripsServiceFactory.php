<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EditTripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripBillsRepository = $entityManager->getRepository('SharengoCore\Entity\TripBills');
        $tripFreeFaresRepository = $entityManager->getRepository('SharengoCore\Entity\TripFreeFares');
        $tripPaymentsRepository = $entityManager->getRepository('SharengoCore\Entity\TripPayments');
        $accountTripsService = $serviceLocator->get('SharengoCore\Service\AccountTripsService');
        $tripCostService = $serviceLocator->get('SharengoCore\Service\TripCostService');
        $tripPaymentsService = $serviceLocator->get('SharengoCore\Service\TripPaymentsService');
        $tripPaymentTriesService = $serviceLocator->get('SharengoCore\Service\TripPaymentTriesService');
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $paymentScriptRunsService = $serviceLocator->get('SharengoCore\Service\PaymentScriptRunsService');
        $preauthorizationsService = $serviceLocator->get('SharengoCore\Service\PreauthorizationsService');

        return new EditTripsService(
            $entityManager,
            $tripBillsRepository,
            $tripFreeFaresRepository,
            $tripPaymentsRepository,
            $accountTripsService,
            $tripCostService,
            $tripPaymentsService,
            $tripPaymentTriesService,
            $customersService,
            $paymentScriptRunsService,
            $preauthorizationsService
        );
    }
}

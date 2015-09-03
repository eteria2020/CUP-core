<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EditTripsServiceFactory extends FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripBillsRepository = $entityManager->getRepository('SharengoCore\Entity\TripBills');
        $tripFreeFaresRepository = $entityManager->getRepository('SharengoCore\Entity\TripFreeFares');
        $tripPaymentsRepository = $entityManager->getRepository('SharengoCore\Entity\TripPayments');
        $accountTripsService = $entityManager->get('SharengoCore\Service\AccountTripsService');
        $tripCostService = $entityManager->get('SharengoCore\Service\TripCostService');

        return new EditTripsServiceFactory(
            $entityManager,
            $tripBillsRepository,
            $tripFreeFaresRepository,
            $tripPaymentsRepository,
            $accountTripsService,
            $tripCostService
        );
    }
}

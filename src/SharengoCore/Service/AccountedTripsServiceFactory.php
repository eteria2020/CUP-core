<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AccountedTripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripBillsRepository = $entityManager->getRepository('SharengoCore\Entity\TripBills');
        $tripBonusesRepository = $entityManager->getRepository('SharengoCore\Entity\TripBonuses');
        $tripFreeFaresRepository = $entityManager->getRepository('SharengoCore\Entity\TripFreeFares');

        return new AccountedTripsService(
            $entityManager,
            $tripBillsRepository,
            $tripBonusesRepository,
            $tripFreeFaresRepository
        );
    }
}

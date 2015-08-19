<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TripCostComputerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = \Mockery::mock('Doctrine\ORM\EntityManager');
        $connection = \Mockery::mock('Doctrine\DBAL\Connection');
        $bonusRepository = \Mockery::mock('SharengoCore\Entity\Repository\CustomersBonusRepository');
        $freeFaresRepository = \Mockery::mock('SharengoCore\Entity\Repository\FreeFaresRepository');
        $bonusService = new BonusService(
            $entityManager,
            $bonusRepository
        );
        $freeFaresService = $serviceLocator->get('SharengoCore\Service\FreeFaresService');

        $accountTripService = new AccountTripsService(
            $entityManager,
            $bonusRepository,
            $freeFaresRepository,
            $bonusService,
            $freeFaresService
        );

        $faresService = $serviceLocator->get('SharengoCore\Service\FaresService');
        $tripFaresService = $serviceLocator->get('SharengoCore\Service\TripFaresService');
        $emailService = \Mockery::mock('SharengoCore\Service\EmailService');
        $cartasiCustomerPayments = \Mockery::mock('Cartasi\Service\CartasiCustomerPayments');

        $tripCostService = new TripCostService(
            $faresService,
            $tripFaresService,
            $entityManager,
            $emailService,
            $cartasiCustomerPayments
        );

        return new TripCostComputerService(
            $accountTripService,
            $entityManager,
            $connection,
            $freeFaresRepository,
            $bonusRepository,
            $tripCostService
        );
    }
}

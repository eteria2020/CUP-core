<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Repository\FreeFaresRepository;
use SharengoCore\Entity\FreeFares;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\Repository\CustomersBonusRepository;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

class TripCostComputerService
{
    /**
     * @var AccountTripService
     */
    private $accountTripsService;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var Connection $connection
     */
    private $connection;

    /**
     * @var FreeFaresRepository $freeFareRepository
     */
    private $freeFareRepository;

    /**
     * @var CustomersBonusRepository $bonusRepository
     */
    private $bonusRepository;

    /**
     * @var TripCostService $tripCostService
     */
    private $tripCostService;

    public function __construct(
        AccountTripsService $accountTripsService,
        EntityManager $entityManager,
        Connection $connection,
        FreeFaresRepository $freeFareRepository,
        CustomersBonusRepository $bonusRepository,
        TripCostService $tripCostService
    ) {
        $this->accountTripsService = $accountTripsService;
        $this->entityManager = $entityManager;
        $this->connection = $connection;
        $this->freeFareRepository = $freeFareRepository;
        $this->bonusRepository = $bonusRepository;
        $this->tripCostService = $tripCostService;
    }

    /**
     * @param DateTime $tripBeginning
     * @param DateTime $tripEnd
     * @param int $tripParkSeconds
     * @param string $customerGender could be 'male' or 'female'
     * @param int $customerBonus
     * @return int cost in eurocents
     */
    public function computeCost(
        \DateTime $tripBeginning,
        \DateTime $tripEnd,
        $tripParkSeconds,
        $customerGender,
        $customerBonus
    ) {
        $customer = $this->buildCustomer($customerGender);
        $trip = $this->buildTrip($tripBeginning, $tripEnd, $tripParkSeconds, $customer);
        $bonus = $this->buildBonus($customerBonus, $tripBeginning, $tripEnd);

        $tripBills = $this->computeTripBills($trip, $bonus);

        $this->setTripBills($trip, $tripBills);

        $tripPayment = $this->tripCostService->retrieveTripCost($trip);

        return $tripPayment->getTotalCost();
    }

    /**
     * @param string $gender could be 'male' or 'female'
     * @return Customers
     */
    private function buildCustomer($gender)
    {
        $customer = new Customers();
        $customer->setGender($gender);

        return $customer;
    }

    /**
     * @param DateTime $tripBeginning
     * @param DateTime $tripEnd
     * @param int $tripParkSeconds
     * @param Customers $customer
     * @return Trips
     */
    private function buildTrip(
        \DateTime $tripBeginning,
        \DateTime $tripEnd,
        $tripParkSeconds,
        Customers $customer
    ) {
        $trip = new Trips();
        $trip->setTImestampBeginning($tripBeginning);
        $trip->setTimestampEnd($tripEnd);
        $trip->setParkSeconds($tripParkSeconds);
        $trip->setCustomer($customer);

        return $trip;
    }

    /**
     * @param int $minutes
     * @param DateTime $beginning
     * @param DateTime $end
     * @return CustomersBonus
     */
    private function buildBonus($minutes, \DateTime $beginning, \DateTime $end)
    {
        $bonus = new CustomersBonus();
        $bonus->setResidual($minutes);
        $bonus->setValidFrom($beginning);
        $bonus->setvalidTo($end);

        return $bonus;
    }

    /**
     * @param Trips $trip
     * @param CustomerBonus
     * @return TripBills[]
     */
    private function computeTripBills(Trips $trip, CustomersBonus $bonus)
    {
        $this->entityManager->shouldReceive('getConnection')->andReturn($this->connection);
        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');

        $this->connection->shouldReceive('beginTransaction');
        $this->connection->shouldReceive('commit');

        $freeFare = new FreeFares();
        $freeFare->setConditions('{"customer":{"gender":"female"},"time":{"from":"01:00","to":"06:00"}}');

        $this->freeFareRepository->shouldReceive('findAllActive')->andReturn([$freeFare]);

        $this->bonusRepository->shouldReceive('getBonusesForTrip')->andReturn([$bonus]);

        return $this->accountTripsService->accountTrip($trip);
    }

    /**
     * @param Trips $trip
     * @param TripBills[] $tripBills
     */
    private function setTripBills(Trips $trip, array $tripBills)
    {
        $tripBillsProperty = new \ReflectionProperty('SharengoCore\Entity\Trips', 'tripBills');
        $tripBillsProperty->setAccessible(true);

        $tripBillsCollection = new ArrayCollection($tripBills);
        $tripBillsPersistentCollection = new PersistentCollection(
            $this->entityManager,
            'SharengoCore\Entity\Trips',
            $tripBillsCollection
        );
        $tripBillsProperty->setValue($trip, $tripBillsPersistentCollection);
    }
}

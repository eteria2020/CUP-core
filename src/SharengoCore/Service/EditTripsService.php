<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripBillsRepository;
use SharengoCore\Entity\Repository\TripFreeFaresRepository;
use SharengoCore\Entity\Repository\TripPaymentsRepository;

use Doctrine\ORM\EntityManager;

class EditTripsService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var TripBillsRepository
     */
    private $tripBillsRepository;

    /**
     * @var TripFreeFaresRepository
     */
    private $tripFreeFaresRepository;

    /**
     * @var TripPaymentsRepository
     */
    private $tripPaymentsRepository;

    /**
     * @var AccountTripsService
     */
    private $accountTripsService;

    /**
     * @var TripCostService
     */
    private $tripCostService;

    public function __construct(
        EntityManager $entityManager,
        TripBillsRepository $tripBillsRepository,
        TripFreeFaresRepository $tripFreeFaresRepository,
        TripPaymentsRepository $tripPaymentsRepository,
        AccountTripsService $accountTripsService,
        TripCostService $tripCostService
    ) {
        $this->entityManager = $entityManager;
        $this->tripBillsRepository = $tripBillsRepository;
        $this->tripFreeFaresRepository = $tripFreeFaresRepository;
        $this->tripPaymentsRepository = $tripPaymentsRepository;
        $this->accountTripsService = $accountTripsService;
        $this->tripCostService = $tripCostService;
    }

    /**
     * NOTICE: DO NOT EXTEND THIS METHOD. IF MORE VARIABLES ARE REQUIRED PLEASE REFACTOR
     *
     * Edit a trip:
     * - modifies the trip fields
     * - deletes all the trip bills
     * - deletes all the trip bonuses and reassigns the customer bonuses
     * - deletes all the trip free fares
     * - deletes all the trip payments (if any)
     * - reaccount trip and recompute trip payment
     *
     * @param Trips $trip
     * @param boolean $notPayable
     * @param DateTime $endDate
     */
    public function editTrip(Trips $trip, $notPayable, \DateTime $endDate)
    {
        $this->entityManager->beginTransaction();

        try {
            // edit trip fields
            $this->doEditTrip($trip, $notPayable, $endDate);

            // delete records linked to the trip
            $this->deleteTripBills($trip);
            // we need to restore the bonuses for the user
            $this->deleteAndReassignTripBonuses($trip);
            $this->deleteTripFreeFares($trip);
            $this->deleteTripPayments($trip);

            // recreate records linked to the trip
            $this->reAccountTrip($trip);
            $this->reComputeTrip($trip);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @param Trips $trip
     * @param boolean $notPayable
     * @param DateTime $endDate
     */
    private function doEditTrip(Trips $trip, $notPayable, \DateTime $endDate)
    {
        $trip->setPayable(!$notPayable);
        $trip->setEndDate($endDate);

        $this->entityManager->persist($trip);
    }

    /**
     * @param $trip
     */
    private function deleteTripBills(Trips $trip)
    {
        $this->tripBillsRepository->deleteTripBillsByTripIds([$trip->getId()]);
    }

    /**
     * @param $trip
     */
    private function deleteAndReassignTripBonuses(Trips $trip)
    {
        foreach ($trip->getTripBonuses() as $tripBonus) {
            $customerBonus = $tripBonus->getBonus();

            // reassign trip bonus minutes
            $customerBonus->incrementResidual($tripBonus->getMinutes());
            $this->entityManager->persist($customerBonus);

            //delete trip bonus
            $this->entityManager->remove($tripBonus);
        }
    }

    /**
     * @param $trip
     */
    private function deleteTripFreeFares(Trips $trip)
    {
        $this->tripFreeFaresRepository->deleteTripFreeFaresByTripIds([$trip->getId()]);
    }

    /**
     * @param $trip
     */
    private function deleteTripPayments(Trips $trip)
    {
        $this->tripPaymentsRepository->deleteTripPaymentsByTrip($trip);
    }

    /**
     * @param $trip
     */
    private function reAccountTrip(Trips $trip)
    {
        $this->accountTripsService->accountTrip($trip);
    }

    /**
     * @param $trip
     */
    private function reComputeTrip(Trips $trip)
    {
        $this->tripCostService->computeTripCost($trip, false);
    }
}

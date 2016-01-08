<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripBillsRepository;
use SharengoCore\Entity\Repository\TripFreeFaresRepository;
use SharengoCore\Entity\Repository\TripPaymentsRepository;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPaymentsCanceled;
use SharengoCore\Entity\TripPaymentTriesCanceled;
use SharengoCore\Entity\Webuser;
use SharengoCore\Exception\EditTripDeniedException;

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

    /**
     * @var TripPaymentsService
     */
    private $tripPaymentsService;

    /**
     * @var TripPaymentTriesService
     */
    private $tripPaymentTriesService;

    /**
     * @param EntityManager $entityManager
     * @param TripBillsRepository $tripBillsRepository
     * @param TripFreeFaresRepository $tripFreeFaresRepository
     * @param TripPaymentsRepository $tripPaymentsRepository
     * @param AccountTripsService $accountTripsService
     * @param TripCostService $tripCostService
     * @param TripPaymentsService $tripPaymentsService
     * @param TripPaymentTriesService $tripPaymentTriesService
     */
    public function __construct(
        EntityManager $entityManager,
        TripBillsRepository $tripBillsRepository,
        TripFreeFaresRepository $tripFreeFaresRepository,
        TripPaymentsRepository $tripPaymentsRepository,
        AccountTripsService $accountTripsService,
        TripCostService $tripCostService,
        TripPaymentsService $tripPaymentsService,
        TripPaymentTriesService $tripPaymentTriesService
    ) {
        $this->entityManager = $entityManager;
        $this->tripBillsRepository = $tripBillsRepository;
        $this->tripFreeFaresRepository = $tripFreeFaresRepository;
        $this->tripPaymentsRepository = $tripPaymentsRepository;
        $this->accountTripsService = $accountTripsService;
        $this->tripCostService = $tripCostService;
        $this->tripPaymentsService = $tripPaymentsService;
        $this->tripPaymentTriesService = $tripPaymentTriesService;
    }

    /**
     * NOTICE: DO NOT EXTEND THIS METHOD. IF MORE VARIABLES ARE REQUIRED PLEASE REFACTOR
     *
     * Edit a trip:
     * - creates backup copies of tripPaymentTries and tripPayments if payment
     *   has begun (only if performed by webuser, throws exception otherwise)
     * - modifies the trip fields
     * - deletes all the trip bills
     * - deletes all the trip bonuses and reassigns the customer bonuses
     * - deletes all the trip free fares
     * - deletes all the trip payments (if any)
     * - reaccount trip and recompute trip payment
     *
     * @param Trips $trip
     * @param boolean $notPayable
     * @param DateTime|null $endDate
     * @param Webuser|null $webuser
     * @throws EditTripDeniedException
     */
    public function editTrip(
        Trips $trip,
        $notPayable,
        $endDate = null,
        Webuser $webuser = null)
    {
        $trip->checkIfEditable($endDate);

        $this->entityManager->beginTransaction();

        try {

            // backup and remove tripPaymentTries if present
            if ($trip->isPaymentTried()) {
                if (!$webuser instanceof Webuser) {
                    throw new EditTripDeniedException();
                }
                $this->cancelTripPaymentTries($trip, $webuser);
            }

            // edit trip fields
            $this->doEditTrip($trip, $notPayable, $endDate);

            // delete records linked to the trip
            $this->deleteTripBills($trip);
            // we need to restore the bonuses for the user
            $this->deleteAndReassignTripBonuses($trip);
            $this->deleteTripFreeFares($trip);
            $this->deleteTripPayments($trip);

            if (!$notPayable) {
                // recreate records linked to the trip
                $this->reAccountTrip($trip);
                $this->reComputeTrip($trip);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * This method is called if TripPaymentTries are present for the trip.
     *
     * First backup copies of the TripPayments are generated. Then those of the
     * TripPaymentTries are. Finally all TripPaymentTries are removed.
     *
     * @param Trips $trip
     * @param Webuser $webuser
     */
    private function cancelTripPaymentTries(Trips $trip, Webuser $webuser)
    {
        foreach ($this->tripPaymentsService->getByTrip($trip) as $tripPayment) {
            $tripPaymentCanceled = new TripPaymentsCanceled(
                $tripPayment,
                $webuser
            );
            $this->entityManager->persist($tripPaymentCanceled);

            foreach ($this->tripPaymentTriesService->getByTripPayment($tripPayment) as $tripPaymentTry) {
                $tripPaymentTryCanceled = new TripPaymentTriesCanceled(
                    $tripPaymentTry,
                    $tripPaymentCanceled
                );
                $this->entityManager->persist($tripPaymentTryCanceled);
                $this->entityManager->remove($tripPaymentTry);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param Trips $trip
     * @param boolean $notPayable
     * @param DateTime|null $endDate
     */
    private function doEditTrip(Trips $trip, $notPayable, $endDate)
    {
        if ($notPayable) {
            $trip->setPayable(false);
        }

        if ($endDate instanceof \DateTime) {
            $trip->setTimestampEnd($endDate);
        }

        $this->entityManager->persist($trip);
    }

    /**
     * @param Trips $trip
     */
    private function deleteTripBills(Trips $trip)
    {
        $this->tripBillsRepository->deleteTripBillsByTripIds([$trip->getId()]);
    }

    /**
     * @param Trips $trip
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
            $this->entityManager->flush();
        }
    }

    /**
     * @param Trips $trip
     */
    private function deleteTripFreeFares(Trips $trip)
    {
        $this->tripFreeFaresRepository->deleteTripFreeFaresByTripIds([$trip->getId()]);
    }

    /**
     * @param Trips $trip
     */
    private function deleteTripPayments(Trips $trip)
    {
        $this->tripPaymentsRepository->deleteTripPaymentsByTrip($trip);
    }

    /**
     * @param Trips $trip
     */
    private function reAccountTrip(Trips $trip)
    {
        $this->accountTripsService->accountTrip($trip);
    }

    /**
     * @param Trips $trip
     */
    private function reComputeTrip(Trips $trip)
    {
        $this->tripCostService->computeTripCost($trip, false);
    }
}

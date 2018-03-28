<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\SafoPenaltyRepository;
use SharengoCore\Entity\SafoPenalty;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Commands\SetCustomerWrongPaymentsAsToBePayed;
use SharengoCore\Exception\TripPaymentWithoutDateException;
// Externals
use Doctrine\ORM\EntityManager;

class FinesService
{
    /**
     * @var TripPayments
     */
    private $tripPaymentsRepository;

    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @var EntityManager
     */
    private $entityManager;

     /**
     * @var FaresService
     */
    private $faresService;

    /**
     * @param TripPaymentsRepository $tripPaymentsRepository
     * @param DatatableServiceInterface $datatableService
     * @param EntityManager $entityManager
     */
    public function __construct(
        SafoPenaltyRepository $safoPenaltyRepository,
        DatatableServiceInterface $datatableService,
        EntityManager $entityManager,
        FaresService $faresService
    ) {
        $this->safoPenaltyRepository = $safoPenaltyRepository;
        $this->datatableService = $datatableService;
        $this->entityManager = $entityManager;
        $this->faresService = $faresService;
    }

    /**
     * @param integer $finesId
     * @return TripPayments
     */
    public function getSafoPenaltyById($finesId)
    {
        return $this->safoPenaltyRepository->findOneById($finesId);
    }

    /**
     * @return [[[[TripPayments]]]]
     */
    public function getTripPaymentsNoInvoiceGrouped($firstDay = null, $lastDay = null)
    {
        return $this->groupTripPayments($this->tripPaymentsRepository->findTripPaymentsNoInvoice($firstDay, $lastDay), $lastDay);
    }

    public function getOneGrouped($tripPaymentId)
    {
        $tripPayment = $this->tripPaymentsRepository->findOneById($tripPaymentId);

        if (!$tripPayment instanceof TripPayments) {
            throw new \Exception('No trip payment present with this id');
        } elseif ($tripPayment->getStatus() !== TripPayments::STATUS_PAYED_CORRECTLY ||
            is_null($tripPayment->getPayedSuccessfullyAt())) {
            throw new \Exception('The trip payment was not correctly payed');
        }

        return $this->groupTripPayments([$tripPayment]);
    }

    /**
     * retrieved the data for the datatable in the admin area
     */
    public function getFinesData(array $filters = [], $count = false)
    {
        $fines = $this->datatableService->getData('SafoPenalty', $filters, $count);
        if ($count) {
            return $fines;
        }

        $a = array_map(function (SafoPenalty $fine) {
            return [
                'fines' => [
                    'id' => $fine->getId(),
                    'charged' => $fine->isCharged(),
                    'customerId' => $fine->getCustomerId(),
                    'vehicleFleetId' => $fine->getVehicleFleetId(),
                    'tripId' => $fine->getTripId(),
                    'carPlate' => $fine->getCarPlate(),
                    'violationAuthority' => $fine->getViolationAuthority(),
                    'violationDescription' => $fine->getViolationDescription(),
                    'amount' => $fine->getAmount(),
                    'complete' => $fine->isComplete()
                ]
            ];
        }, $fines);
        return $a;
    }

    public function getTotalFines()
    {
        return $this->safoPenaltyRepository->countTotalFines();
    }

    /**
     * @param null $timestampEndParam
     * @param null $condition
     * @param null $limit
     * @return array
     */
}

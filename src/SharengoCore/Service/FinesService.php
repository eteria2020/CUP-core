<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\SafoPenaltyRepository;
use SharengoCore\Entity\SafoPenalty;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Service\FleetService;
// Externals
use Doctrine\ORM\EntityManager;

class FinesService
{
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
    private $fleetService;

    /**
     * @param TripPaymentsRepository $tripPaymentsRepository
     * @param DatatableServiceInterface $datatableService
     * @param EntityManager $entityManager
     */
    public function __construct(
        SafoPenaltyRepository $safoPenaltyRepository,
        DatatableServiceInterface $datatableService,
        EntityManager $entityManager,
        FleetService $fleetService
    ) {
        $this->safoPenaltyRepository = $safoPenaltyRepository;
        $this->datatableService = $datatableService;
        $this->entityManager = $entityManager;
        $this->fleetService = $fleetService;
    }

    /**
     * @param integer $finesId
     * @return SafoPenalty
     */
    public function getSafoPenaltyById($finesId)
    {
        return $this->safoPenaltyRepository->findOneById($finesId);
    }

    /**
     * retrieved the data for the datatable in the admin area
     */
    public function getFinesData(array $filters = [], $count = false)
    {
        if(isset($filters['searchValue'])&&($filters['searchValue']!="")){
            if($filters['column']=="e.vehicleFleetId"){
                $fleets = $this->fleetService->getFleetsSelectorArray();
                foreach ($fleets as $i => $fleet) {
                    if(strtolower($filters['searchValue'])==strtolower($fleet)){
                        $filters['searchValue']=$i;
                    }
                }
            }
        }

        $fines = $this->datatableService->getData('SafoPenalty', $filters, $count);
        if ($count) {
            return $fines;
        }

        $a = array_map(function (SafoPenalty $fine) {
            return [
                'fines' => [
                    'id' => $fine->getId(),
                    'checkable' => $this->isCheckable($fine),
                    'charged' => $fine->isCharged(),
                    'customerId' => $fine->getCustomerId(),
                    'vehicleFleetId' => $fine->getVehicleFleetId(),
                    'tripId' => $fine->getTripId(),
                    'carPlate' => $fine->getCarPlate(),
                    'violationAuthority' => $fine->getViolationAuthority(),
                    'violationDescription' => $fine->getViolationDescription(),
                    'amount' => $fine->getAmount(),
                    'complete' => $fine->isComplete(),
                    'violationTimestamp' => $fine->getViolationTimestamp()->format('Y/m/d H:i:s')
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
    
    
    
    public function isCheckable($fine) {
        if($fine->getCharged()){
            return 0;
        }else{
            if(!is_null($fine->getCustomerId()) && !is_null($fine->getTripId()) && $fine->isComplete()){
                return 1;
            }else{
                return 2;
            }
        }
    }
    
    public function getFinesBetweenDate($from, $to) {
        return $this->safoPenaltyRepository->getFinesBetweenDate($from, $to);
    }
}

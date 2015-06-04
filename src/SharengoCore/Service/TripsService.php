<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Trips;

class TripsService
{
    /** @var TripsRepository */
    private $tripRepository;

    /**
     * @var DatatableService
     */
    private $I_datatableService;

    /**
     * @param EntityRepository $tripRepository
     */
    public function __construct($tripRepository, DatatableService $I_datatableService)
    {
        $this->tripRepository = $tripRepository;
        $this->I_datatableService = $I_datatableService;
    }

    /**
     * @return mixed
     */
    public function getTripsByCustomer($customerId)
    {
        return $this->tripRepository->findTripsByCustomer($customerId);
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $trips = $this->I_datatableService->getData('Trips', $as_filters);

        return array_map(function (Trips $trip) {
            return [
                'id'            => $trip->getId(),
                'name'          => $trip->getCustomer()->getName(),
                'surname'       => $trip->getCustomer()->getSurname(),
                'card'          => $trip->getCustomer()->getCardCode(),
                'mobile'        => $trip->getCustomer()->getMobile(),
                'plate'         => $trip->getCar()->getPlate(),
                'kmBeginning'   => $trip->getKmBeginning(),
                'kmEnd'         => $trip->getKmEnd(),
                'timeBeginning' => $trip->getTimestampBeginning()->format('d.m.Y H:i:s'),
                'timeEnd'       => $trip->getTimestampEnd()->format('d.m.Y H:i:s'),
                'parkSeconds'   => $trip->getParkSeconds() . ' sec'
            ];
        }, $trips);
    }

    public function getTotalTrips()
    {
        return $this->tripRepository->getTotalTrips();
    }
}
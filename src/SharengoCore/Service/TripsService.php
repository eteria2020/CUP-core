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
     * @var DatatableServiceInterface
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

            $user = sprintf(
                '<a href="%s">%s %s %s</a>',
                '/customers/edit/' . $trip->getCustomer()->getId(),
                $trip->getCustomer()->getName(),
                $trip->getCustomer()->getSurname(),
                $trip->getCustomer()->getMobile()
            );

            $plate = sprintf(
                '<a href="%s">%s</a>',
                '/cars/edit/' . $trip->getCar()->getPlate(),
                $trip->getCar()->getPlate()
            );

            return [
                'id'               => $trip->getId(),
                'user'             => $user,
                'plate'            => $plate,
                'card'             => $trip->getCustomer()->getCardCode(),
                'km'               => ($trip->getKmEnd() - $trip->getKmBeginning()),
                'price'            => ($trip->getPriceCent() + $trip->getVatCent()),
                'addressBeginning' => $trip->getAddressBeginning(),
                'addressEnd'       => $trip->getAddressEnd(),
                'timeBeginning'    => $trip->getTimestampBeginning()->format('d.m.Y H:i:s'),
                'timeEnd'          => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('d.m.Y H:i:s') : ''),
                'payable'          => $trip->getPayable() ? 'Si' : 'No',
                'parkSeconds'      => $trip->getParkSeconds() . ' sec'
            ];
        }, $trips);
    }

    public function getTotalTrips()
    {
        return $this->tripRepository->getTotalTrips();
    }
}

<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Trips;

class TripsService
{
    const DURATA_NON_DISPONIBILE = 'n.d.';

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

            $plate = sprintf(
                '<a href="%s">%s</a>',
                '/cars/edit/' . $trip->getCar()->getPlate(),
                $trip->getCar()->getPlate()
            );

            return [
                'e-id'                 => $trip->getId(),
                'cu-surname'           => $trip->getCustomer()->getSurname(),
                'cu-name'              => $trip->getCustomer()->getName(),
                'cu-mobile'            => $trip->getCustomer()->getMobile(),
                'cu-cardCode'          => $trip->getCustomer()->getCardCode(),
                'c-plate'              => $plate,
                'c-label'              => $trip->getCar()->getLabel(),
                'e-kmBeginning'        => $trip->getKmBeginning(),
                'e-kmEnd'              => $trip->getKmEnd(),
                'e-timestampBeginning' => $trip->getTimestampBeginning()->format('H:i:s'),
                'e-timestampEnd'       => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('H:i:s') : ''),
                'duration'             => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd()),
                'e-parkSeconds'        => $trip->getParkSeconds() . ' sec',
                'price'                => ($trip->getPriceCent() + $trip->getVatCent()),
                'StatoQuadro'          => '',
                'c-parking'            => $trip->getCar()->getParking() ? 'Si' : 'No',
                'e-payable'            => $trip->getPayable() ? 'Si' : 'No',
            ];
        }, $trips);
    }

    public function getTotalTrips()
    {
        return $this->tripRepository->getTotalTrips();
    }

    public function getDuration($s_from, $s_to)
    {
        if ('' != $s_from && '' != $s_to) {

            $date = $s_from->diff($s_to);

            $days = (int)$date->format('%d');

            if ($days > 0) {
                return sprintf('%sg %s:%s:%s', $days, $date->format('%H'), $date->format('%I'), $date->format('%S'));
            } else {
                return sprintf('0g %s:%s:%s', $date->format('%H'), $date->format('%I'), $date->format('%S'));
            }

        }

        return self::DURATA_NON_DISPONIBILE;
    }
}

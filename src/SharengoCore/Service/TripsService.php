<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Trips;

use Zend\View\Helper\Url;

class TripsService
{
    const DURATION_NOT_AVAILABLE = 'n.d.';

    /** @var TripsRepository */
    private $tripRepository;

    /**
     * @var DatatableServiceInterface
     */
    private $I_datatableService;

    /** @var */
    private $I_urlHelper;

    public function __construct(
        TripsRepository $tripRepository,
        DatatableService $I_datatableService,
        $I_urlHelper
    ) {
        $this->tripRepository = $tripRepository;
        $this->I_datatableService = $I_datatableService;
        $this->I_urlHelper = $I_urlHelper;
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

            $urlHelper = $this->I_urlHelper;
            $plate = sprintf(
                '<a href="%s">%s</a>',
                $urlHelper(
                    'cars/edit',
                    ['plate' => $trip->getCar()->getPlate()]
                ),
                $trip->getCar()->getPlate()
            );

            return [
                'e'        => [
                    'id'                 => $trip->getId(),
                    'kmBeginning'        => $trip->getKmBeginning(),
                    'kmEnd'              => $trip->getKmEnd(),
                    'timestampBeginning' => $trip->getTimestampBeginning()->format('d-m-Y H:i:s'),
                    'timestampEnd'       => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('d-m-Y H:i:s') : ''),
                    'parkSeconds'        => $trip->getParkSeconds() . ' sec',
                    'payable'            => $trip->getPayable() ? 'Si' : 'No',
                ],
                'cu'       => [
                    'surname' => $trip->getCustomer()->getSurname(),
                    'name'    => $trip->getCustomer()->getName(),
                    'mobile'  => $trip->getCustomer()->getMobile(),
                ],
                'c'        => [
                    'plate'     => $plate,
                    'label'     => $trip->getCar()->getLabel(),
                    'parking'   => $trip->getCar()->getParking() ? 'Si' : 'No',
                    'keyStatus' => $trip->getCar()->getKeystatus()
                ],
                'cc'       => [
                    'code' => is_object($trip->getCustomer()->getCard()) ? $trip->getCustomer()->getCard()->getCode() : '',

                ],
                'duration' => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd()),
                'price'    => ($trip->getPriceCent() + $trip->getVatCent()),

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

        return self::DURATION_NOT_AVAILABLE;
    }

    public function getUrlHelper()
    {
        return $this->I_viewHelperManager->get('url');
    }

    public function getTripsToBeAccounted()
    {
        return $this->tripRepository->findTripsToBeAccounted();
    }

    public function getTripById($tripId)
    {
        return $this->tripRepository->findOneById($tripId);
    }
}

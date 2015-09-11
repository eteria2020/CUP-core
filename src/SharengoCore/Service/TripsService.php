<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\CustomersService;
use SharengoCore\Service\TripPaymentsService;

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

    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var TripPaymentsService
     */
    private $tripPaymentsService;

    /**
     * @param EntityRepository $tripRepository
     * @param DatatableService $I_datatableService
     * @param \\TODO $I_urlHelper
     * @param CustomersService $customersService
     * @param TripPaymentsService $tripPaymentsService
     */
    public function __construct(
        $tripRepository,
        DatatableService $I_datatableService,
        $I_urlHelper,
        CustomersService $customersService,
        TripPaymentsService $tripPaymentsService
    ) {
        $this->tripRepository = $tripRepository;
        $this->I_datatableService = $I_datatableService;
        $this->I_urlHelper = $I_urlHelper;
        $this->customersService = $customersService;
        $this->tripPaymentsService = $tripPaymentsService;
    }

    /**
     * @return mixed
     */
    public function getListTrips()
    {
        return $this->tripRepository->findAll();
    }

    /**
     * @return mixed
     */
    public function getTripsByCustomer($customerId)
    {
        return $this->tripRepository->findTripsByCustomer($customerId);
    }

    public function getListTripsFiltered($filters = [])
    {
        return $this->tripRepository->findBy($filters, ['timestampEnd' => 'DESC']);
    }

    public function getListTripsFilteredLimited($filters = [], $limit)
    {
        return $this->tripRepository->findBy($filters, ['timestampEnd' => 'DESC'], $limit);
    }

    public function getTripsByPlateNotEnded($plate)
    {
        return $this->tripRepository->findTripsByPlateNotEnded($plate);
    }

    public function getTripsByCustomerNotEnded($customer)
    {
        return $this->tripRepository->findTripsByCustomerNotEnded($customer);
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

            $parentId = "";
            $parentStart = "";
            $parent = $trip->getParent();
            if ($parent !== null) {
                $parentId = "<br>(" . $parent->getId() . ")";
                $parentStart = "<br>(" . $parent->getTimestampBeginning()->format('d-m-Y H:i:s') . ")";
            }

            $tripCost = '';
            if ($trip->getPayable()) {
                $tripPayment = $this->tripPaymentsService->getTripPaymentForTrip($trip);
                if ($tripPayment instanceof TripPayments) {
                    $tripCost = $tripPayment->getTotalCost();
                }
            } else {
                $tripCost = 'FREE';
            }

            return [
                'e' => [
                    'id' => $trip->getId() . $parentId,
                    'kmBeginning' => $trip->getKmBeginning(),
                    'kmEnd' => $trip->getKmEnd(),
                    'timestampBeginning' => $trip->getTimestampBeginning()->format('d-m-Y H:i:s') . $parentStart,
                    'timestampEnd' => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('d-m-Y H:i:s') : ''),
                    'parkSeconds' => $trip->getParkSeconds() . ' sec',
                    'payable' => $trip->getPayable() ? 'Si' : 'No',
                    'totalCost' => ['amount' => $tripCost, 'id' => $trip->getId()],
                    'idLink' => $trip->getId()
                ],
                'cu'       => [
                    'surname' => $trip->getCustomer()->getSurname(),
                    'name'    => $trip->getCustomer()->getName(),
                    'mobile'  => $trip->getCustomer()->getMobile()
                ],
                'c'        => [
                    'plate'     => $plate,
                    'label'     => $trip->getCar()->getLabel(),
                    'parking'   => $trip->getCar()->getParking() ? 'Si' : 'No',
                    'keyStatus' => $trip->getCar()->getKeystatus()
                ],
                'cc'       => [
                    'code' => is_object($trip->getCustomer()->getCard()) ? $trip->getCustomer()->getCard()->getCode() : ''

                ],
                'duration' => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd())
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
            return $s_from->diff($s_to)->format('%dg %H:%I:%S');
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

    /**
     * @param Customers
     * @return Trips[]
     */
    public function getCustomerTripsToBeAccounted(Customers $customer)
    {
        return $this->tripRepository->findCustomerTripsToBeAccounted($customer);
    }

    public function getLastTrip($plate)
    {
        return $this->tripRepository->findLastTrip($plate);
    }

    public function getTripsByUsersInGoldList()
    {
        return $this->tripRepository->findTripsByUsersInGoldList();
    }

    public function setTripsAsNotPayable($tripIds)
    {
        return $this->tripRepository->updateTripsPayable($tripIds, false);
    }

    public function setTripAsNotPayable(Trips $trip)
    {
        return $this->setTripsAsNotPayable([$trip->getId()]);
    }

    /**
     * retrieves all the trips that we need to process to compute the cost
     */
    public function getTripsForCostComputation()
    {
        return $this->tripRepository->findTripsForCostComputation();
    }

    /**
     * @param Customers $customer
     * @return mixed
     */
    public function getDistinctDatesForCustomerByMonth($customer)
    {
        $dates = $this->tripRepository->findDistinctDatesForCustomerByMonth($customer);
        $returnDates = [];
        foreach ($dates as $date) {
            $returnDates[$date['timestampBeginning']->format('Y-m')] = $date['timestampBeginning'];
        }
        return $returnDates ;
    }

    /**
     * @param string $month
     * @param integer $customer
     * @return Trips[]
     */
    public function getListTripsForMonthByCustomer($month, $customerId)
    {
        return $this->tripRepository->findListTripsForMonthByCustomer($month, $customerId);
    }

    /**
     * @param integer $limit
     * @return Trips[]
     */
    public function getTripsNoAddress($limit = 0)
    {
        return $this->tripRepository->findTripsNoAddress($limit);
    }
}

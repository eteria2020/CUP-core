<?php

namespace SharengoCore\Service;

use Application\Form\InputData\CloseTripData;
use SharengoCore\Entity\Commands;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\WebUser;
use SharengoCore\Service\CommandsService;
use SharengoCore\Service\CustomersService;

use Zend\View\Helper\Url;

class TripsService
{
    const DURATION_NOT_AVAILABLE = 'n.d.';

    /** @var TripsRepository */
    private $tripRepository;

    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @var Url
     */
    private $I_urlHelper;

    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var CommandsService
     */
    private $commandsService;

    /**
     * @param EntityRepository $tripRepository
     * @param DatatableService $datatableService
     * @param \\TODO $I_urlHelper
     * @param CustomersService $customersService
     */
    public function __construct(
        $tripRepository,
        DatatableService $datatableService,
        $I_urlHelper,
        CustomersService $customersService,
        CommandsService $commandsService
    ) {
        $this->tripRepository = $tripRepository;
        $this->datatableService = $datatableService;
        $this->I_urlHelper = $I_urlHelper;
        $this->customersService = $customersService;
        $this->commandsService = $commandsService;
    }

    /**
     * @param integer $id
     * @return Trips
     */
    public function getById($id)
    {
        return $this->tripRepository->findOneById($id);
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

    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $trips = $this->datatableService->getData('Trips', $as_filters, $count);

        if ($count) {
            return $trips;
        }

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

            if ($parent !== null && $parent instanceof Trips && $parent->getId() != -1) {
                $parentId = "<br>(" . $parent->getId() . ")";
                $parentStart = "<br>(" . $parent->getTimestampBeginning()->format('d-m-Y H:i:s') . ")";
            }

            /**
             * blank - the trip has not ended
             * 'FREE' - the trip is free because the customer is either in gold
             *     list or is a maintainer
             * n,nn (the actual cost) - if the trip has a cost greater than zero
             * 0,00 - if the cost has not yet been calculated or bonus minutes
             *     have been used
             */
            $tripCost = '';
            if ($trip->isEnded()) {
                if ($trip->getPayable() && $trip->isAccountable()) {
                    $tripPayment = $trip->getTripPayment();
                    if ($tripPayment instanceof TripPayments) {
                        $tripCost = $tripPayment->getTotalCost();
                    } else {
                        $tripCost = 0;
                    }
                } else {
                    $tripCost = 'FREE';
                }
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
                'cu' => [
                    'id' => $trip->getCustomer()->getId(),
                    'email' => $trip->getCustomer()->getEmail(),
                    'surname' => $trip->getCustomer()->getSurname(),
                    'name' => $trip->getCustomer()->getName(),
                    'mobile' => $trip->getCustomer()->getMobile()
                ],
                'c' => [
                    'plate' => $plate,
                    'label' => $trip->getCar()->getLabel(),
                    'parking' => $trip->getCar()->getParking() ? 'Si' : 'No',
                    'keyStatus' => $trip->getCar()->getKeystatus()
                ],
                'cc' => [
                    'code' => is_object($trip->getCustomer()->getCard()) ? $trip->getCustomer()->getCard()->getCode() : ''

                ],
                'f' => [
                    'name' => $trip->getFleetName(),
                ],
                'duration' => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd()),
                'payed' => $trip->getPayable() ? ($trip->isPaymentCompleted() ? 'Si' : 'No') : '-'
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

    /**
     * @param CloseTripData $closeTrip
     */
    public function closeTrip(CloseTripData $closeTrip, WebUser $webUser)
    {
        $this->commandsService->sendCommand(
            $closeTrip->car(),
            Commands::CLOSE_TRIP,
            $webUser
        );

        $this->tripRepository->closeTrip(
            $closeTrip->trip(),
            $closeTrip->dateTime(),
            $closeTrip->payable()
        );
    }

    /**
     * Returns an array of Trips to be displayed in a datatable that represents
     * trips that have not yet been payed
     *
     * @return Trips[]
     */
    public function getTripsNotPayedData()
    {
        return $this->tripRepository->findTripsNotPayedData();
    }
}

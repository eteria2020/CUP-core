<?php

namespace SharengoCore\Service;

// Internals
use Application\Form\InputData\CloseTripData;
use SharengoCore\Entity\Commands;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\WebUser;
use SharengoCore\Service\CommandsService;
use SharengoCore\Service\CustomersService;
use SharengoCore\Service\LocationService;
// Externals
use Zend\Mvc\I18n\Translator;
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
     * @var DatatableServiceInterface
     */
    private $datatableServiceNotPayed;

    /**
     * @var Url
     */
    private $urlHelper;

    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var CommandsService
     */
    private $commandsService;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LocationService
     */
    private $locationService;

    /**
     * @param EntityRepository $tripRepository
     * @param DatatableServiceInterface $datatableService
     * @param DatatableServiceInterface $datatableServiceNotPayed
     * @param \\TODO $urlHelper
     * @param CustomersService $customersService
     * @param CommandsService $commandsService
     * @param Translator $translator
     * @param LocationService $locationService
     */
    public function __construct(
        TripsRepository $tripRepository,
        DatatableServiceInterface $datatableService,
        DatatableServiceInterface $datatableServiceNotPayed,
        Url $urlHelper,
        CustomersService $customersService,
        CommandsService $commandsService,
        Translator $translator,
        LocationService $locationService
    ) {
        $this->tripRepository = $tripRepository;
        $this->datatableService = $datatableService;
        $this->datatableServiceNotPayed = $datatableServiceNotPayed;
        $this->urlHelper = $urlHelper;
        $this->customersService = $customersService;
        $this->commandsService = $commandsService;
        $this->translator = $translator;
        $this->locationService =$locationService;
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
     * 
     * @param Trip $trip
     */
    public function getBusinessByTrip(Trips $trip){
        return $this->tripRepository->findBusinessByTrip($trip);
    }

    /**
     * 
     * @param Trip $trip
     */
    public function getBusinessFareByTrip(Trips $trip){
        return $this->tripRepository->findBusinessFareByTrip($trip);
    }

     /**
     * 
     * @param Trip $trip
     */
    public function getBusinessTripPayment(Trips $trip){
        return $this->tripRepository->findBusinessTripPaymentByTrip($trip);
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
    
    public function getTripsByCustomerCO2($customerId)
    {
            return $this->tripRepository->findTripsByCustomerCO2($customerId);
    }
    
    public function  getTripsByCustomerForAddPointYesterday($customerId, $dateYesterdayStart, $dateTodayStart){
        return $this->tripRepository->getTripsByCustomerForAddPointYesterday($customerId, $dateYesterdayStart, $dateTodayStart);
    }
    
    public function  getTripsByCustomerForAddPointMonth($customerId, $dateCurrentMonthStart, $dateYesterdayStart){
        return $this->tripRepository->getTripsByCustomerForAddPointMonth($customerId, $dateCurrentMonthStart, $dateYesterdayStart);
    }
    
    public function  getTripsByCustomerForAddPointClusterLastMonth($customerId, $dateStartLastMonth, $dateStartCurrentMonth){
        return $this->tripRepository->getTripsByCustomerForAddPointClusterLastMonth($customerId, $dateStartLastMonth, $dateStartCurrentMonth);
    }
    
    public function  getTripsByCustomerForAddPointClusterTwotMonthAgo($customerId, $dateCurrentMonthStart, $dateYesterdayStart){
        return $this->tripRepository->getTripsByCustomerForAddPointClusterTwotMonthAgo($customerId, $dateStartLastMonth, $dateStartTwotMonthAgo);
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

    public function getDataDataTable(array $filters = [], $count = false)
    {
        $trips = $this->datatableService->getData('Trips', $filters, $count);

        if ($count) {
            return $trips;
        }

        return array_map(function (Trips $trip) {

            $plate = $this->getClickablePlate($trip);

            $parentId = "";
            $parentStart = "";
            $parent = $trip->getParent();

            if ($parent !== null && $parent instanceof Trips && $parent->getId() != -1) {
                $parentId = "<br>(" . $parent->getId() . ")";
                $parentStart = "<br>(" . $parent->getTimestampBeginning()->format('d-m-Y H:i:s') . ")";
            }

            $tripCost = $this->calculateTripCost($trip);

            return [
                'e' => [
                    'id' => $trip->getId() . $parentId,
                    'kmBeginning' => $trip->getKmBeginning(),
                    'kmEnd' => $trip->getKmEnd(),
                    'timestampBeginning' => $trip->getTimestampBeginning()->format('d-m-Y H:i:s') . $parentStart,
                    'timestampEnd' => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('d-m-Y H:i:s') : ''),
                    'parkSeconds' => $trip->getParkSeconds() . ' sec',
                    'payable' => $trip->getPayable() ? $this->translator->translate('Si') : $this->translator->translate('No'),
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
                    'parking' => $trip->getCar()->getParking() ? $this->translator->translate('Si') : $this->translator->translate('No'),
                    'keyStatus' => $trip->getCar()->getKeystatus()
                ],
                'cc' => [
                    'code' => is_object($trip->getCustomer()->getCard()) ? $trip->getCustomer()->getCard()->getCode() : ''

                ],
                'f' => [
                    'name' => $trip->getFleetName(),
                ],
                'duration' => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd()),
                'payed' => $trip->getPayable() ? ($trip->isPaymentCompleted() ? $this->translator->translate('Si') : $this->translator->translate('No')) : '-'
            ];
        }, $trips);
    }

    public function getDataNotPayedDataTable(array $filters = [], $count = false)
    {
        $trips = $this->datatableServiceNotPayed->getData('TripsNotPayed', $filters, $count);

        if ($count) {
            return $trips;
        }

        return array_map(function (Trips $trip) {

            $plate = $this->getClickablePlate($trip);
            $tripCost = $this->calculateTripCost($trip);

            return [
                'e' => [
                    'id' => $trip->getId(),
                    'kmBeginning' => $trip->getKmBeginning(),
                    'kmEnd' => $trip->getKmEnd(),
                    'timestampBeginning' => $trip->getTimestampBeginning()->format('d-m-Y H:i:s'),
                    'timestampEnd' => (null != $trip->getTimestampEnd() ? $trip->getTimestampEnd()->format('d-m-Y H:i:s') : ''),
                    'duration' => $this->getDuration($trip->getTimestampBeginning(), $trip->getTimestampEnd()),
                    'parkSeconds' => $trip->getParkSeconds() . ' sec',
                    'totalCost' => ['amount' => $tripCost, 'id' => $trip->getId()],
                ],
                'cu' => [
                    'id' => $trip->getCustomer()->getId(),
                    'surname' => $trip->getCustomer()->getSurname() ,
                    'name' => $trip->getCustomer()->getName()
                ],
                'cc' => [
                    'rfid' => $trip->getCustomer()->getCard()->getRfid(),
                ],
                'c' => [
                    'plate' => $plate,
                ],
                'f'=>[
                    'name' => $trip->getCar()->getFleet()->getName()
                ]
            ];
        }, $trips);
    }

    private function getClickablePlate(Trips $trip)
    {
        $urlHelper = $this->urlHelper;
        return sprintf(
            '<a href="%s">%s</a>',
            $urlHelper(
                'cars/edit',
                ['plate' => $trip->getCar()->getPlate()]
            ),
            $trip->getCar()->getPlate()
        );
    }

    /**
     * blank - the trip has not ended
     * 'FREE' - the trip is free because the customer is either in gold
     *     list or is a maintainer
     * n,nn (the actual cost) - if the trip has a cost greater than zero
     * 0,00 - if the cost has not yet been calculated or bonus minutes
     *     have been used
     * @param Trips $trip
     * @return float|string
     */
    private function calculateTripCost(Trips $trip)
    {
        if ($trip->isEnded()) {
            if ($trip->getPayable() && $trip->isAccountable()) {
                $tripPayment = $trip->getTripPayment();
                if ($tripPayment instanceof TripPayments) {
                    return $tripPayment->getTotalCost();
                } else {
                    return 0;
                }
            } else {
                return 'FREE';
            }
        }
        return '';
    }

    public function getTotalTrips()
    {
        return $this->tripRepository->getTotalTrips();
    }

    public function getTotalTripsNotPayed()
    {
        return $this->tripRepository->getTotalTripsNotPayed();
    }

    public function getDuration($from, $to)
    {
        if ('' != $from && '' != $to) {
            return $from->diff($to)->format('%dg %H:%I:%S');
        }

        return $this->translator->translate(self::DURATION_NOT_AVAILABLE);
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
     * @param Customers $customer
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
     * retrieves all the trips that we need to process to compute the bonuses
     */
    public function getTripsForBonusComputation()
    {
        return $this->tripRepository->findTripsForBonusComputation();
    }

    /**
     * retrieves all the trips that we need to process to extra fares
     */
    public function getTripsForExtraFareComputation()
    {
        $result = array_merge($this->tripRepository->findTripsForExtraFareToBePayed(), 
            $this->tripRepository->findTripsForExtraFareNullTripPayments());
        return $result;
    }

    /**
     * retrieves all the trips that we need to process to compute the bonuses park islands
     */
    public function getTripsForBonusParkComputation($datestamp,$carplate)
    {
        return $this->tripRepository->findTripsForBonusParkComputation($datestamp, $carplate);
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

    public function setAddressByGeocode(Trips $trip, $dryRun = false, $addressBeginningPostfix = "", $addressEndPostfix = ""){
        $result = null;

        if(isset($trip)){
            $delay = 100000; // half second in microseconds

            $addressBeginning = $this->locationService->getAddressFromCoordinates(
                $trip->getLatitudeBeginning(),
                $trip->getLongitudeBeginning()
            );

            if(is_null($addressBeginning)){
                $addressBeginning="";
            }

            $trip->setAddressBeginning($addressBeginning);

            $addressEnd = $this->locationService->getAddressFromCoordinates(
                $trip->getLatitudeEnd(),
                $trip->getLongitudeEnd()
            );

            if(is_null($addressEnd)){
                $addressEnd="";
            }

            $trip->setAddressEnd($addressEnd);

            if (!$dryRun) {
                $result = $this->tripRepository->updateTripsAdrress($trip, 
                    $addressBeginning.$addressBeginningPostfix, 
                    $addressEnd.$addressEndPostfix);
            }

            usleep($delay);
        }

        return $result;
    }

    /**
     * Return the array and the total cost of the trips in status 'to_be_pay' and 'wrong'
     * 
     * @param Customers $customer Customer
     * @param Trips[] $trips Array of trips
     * @return int Return total cost of trips
     */
    public function getTripsToBePayedAndWrong(Customers $customer, &$trips){

        $result =0;
        $trips = $this->tripRepository->findTripsToBePayedAndWrong($customer);

        foreach($trips as $trip) {
            $result += $trip->getTripPayment()->getTotalCost();
        }

        return $result;
    }
}

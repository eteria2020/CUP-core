<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\TripPaymentsRepository;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Commands\SetCustomerWrongPaymentsAsToBePayed;
use SharengoCore\Exception\TripPaymentWithoutDateException;
// Externals
use Doctrine\ORM\EntityManager;

class TripPaymentsService
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
        TripPaymentsRepository $tripPaymentsRepository,
        DatatableServiceInterface $datatableService,
        EntityManager $entityManager,
        FaresService $faresService
    ) {
        $this->tripPaymentsRepository = $tripPaymentsRepository;
        $this->datatableService = $datatableService;
        $this->entityManager = $entityManager;
        $this->faresService = $faresService;
    }

    /**
     * @param integer $tripPaymentId
     * @return TripPayments
     */
    public function getTripPaymentById($tripPaymentId)
    {
        return $this->tripPaymentsRepository->findOneById($tripPaymentId);
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
     * Groups the tripPayments first by date, then by customer and finally by fleet
     * @param [TripPayments] $tripPayments
     * @return [[[[TripPayments]]]]
     */
    private function groupTripPayments($tripPayments, $lastDay = null)
    {
        if ($lastDay instanceof \DateTime) {
            $date = $lastDay->format("Y-m-d");
        }
        // group by date and customer
        $orderedTripPayments = [];
        foreach ($tripPayments as $tripPayment) {
            $dateTrip = $tripPayment->getPayedSuccessfullyAt();
            if (!$dateTrip instanceof \DateTime) {
                throw new TripPaymentWithoutDateException($tripPayment);
            }
            if (!$lastDay instanceof \DateTime) {
                // retrieve date and customerId from tripPayment
                $date = $dateTrip->format('Y-m-d');
            }
            $customerId = $tripPayment->getTrip()->getCustomer()->getId();
            $fleetId = $tripPayment->getTrip()->getFleet()->getId();
            // if first tripPayment for that day, create the entry
            if (isset($orderedTripPayments[$date])) {
                // if first tripPayment for that customer, create the entry
                if (!isset($orderedTripPayments[$date][$customerId])) {
                    $orderedTripPayments[$date][$customerId] = [$fleetId => []];
                // if first tripPayment for that fleet, create the entry
                } elseif (!isset($orderedTripPayments[$date][$customerId][$fleetId])) {
                    $orderedTripPayments[$date][$customerId][$fleetId] = [];
                }
            } else {
                $orderedTripPayments[$date] = [$customerId => [$fleetId => []]];
            }
            // add the tripPayment in the correct group
            array_push($orderedTripPayments[$date][$customerId][$fleetId], $tripPayment);
        }

        // sort payments according to their date
        ksort($orderedTripPayments);

        return $orderedTripPayments;
    }

    /**
     * retrieved the data for the datatable in the admin area
     */
    public function getFailedPaymentsData(array $filters = [], $count = false)
    {
        $payments = $this->datatableService->getData('TripPayments', $filters, $count);

        if ($count) {
            return $payments;
        }

        return array_map(function (TripPayments $payment) {
            $customer = $payment->getTrip()->getCustomer();
            $firstPaymentTryTs = (!is_null( $payment->getFirstPaymentTryTs()) ? $payment->getFirstPaymentTryTs()->format('Y-m-d H:i:s'): $payment->getCreatedAt()->format('Y-m-d H:i:s')); 
            return [
                'e' => [
                    'firstPaymentTryTs' => $firstPaymentTryTs,
                    'trip' => $payment->getTrip()->getId(),
                    'tripMinutes' => $payment->getTripMinutes(),
                    'parkingMinutes' => $payment->getParkingMinutes(),
                    'discountPercentage' => $payment->getDiscountPercentage(),
                    'totalCost' => $payment->getTotalCost()
                ],
                'cu' => [
                    'id' => $customer->getId(),
                    'name' => $customer->getName(),
                    'surname' => $customer->getSurname(),
                    'mobile' => $customer->getMobile(),
                    'email' => $customer->getEmail(),
                    'type' => ($customer->getGoldList() || $customer->getMaintainer()) ? true : false 
                ],
                'button' => $payment->getId()
            ];
        }, $payments);
    }

    public function getTotalFailedPayments()
    {
        return $this->tripPaymentsRepository->countTotalFailedPayments();
    }

    /**
     * @param Customers $customer optional parameter to filter the results by
     *  customer
     * @return PersistentCollection
     */
    public function getTripPaymentsForPayment(Customers $customer = null, $timestampEndParam = null, $condition = null, $limit = null)
    {
        return $this->tripPaymentsRepository->findTripPaymentsForPayment($customer, $timestampEndParam, $condition, $limit);
    }

    public function getTripPaymentsForRefund(Customers $customer = null, $timestampEndParam = null)
    {
        return $this->tripPaymentsRepository->findTripPaymentsForRefund($customer, $timestampEndParam);
    }

    public function getTripPaymentsWrongTime(Customers $customer = null, $start, $end, $condition = null, $limit = null)
    {
        return $this->tripPaymentsRepository->findWrongTripPaymentsTime($customer, $start, $end, $condition, $limit);
    }

    public function getTripPaymentsWrong(Customers $customer = null, $timestampEndParam = null)
    {
        return $this->tripPaymentsRepository->findTripPaymentsWrong($customer, $timestampEndParam);
    }

    public function getTripPaymentsToBePayedAndWrong(Customers $customer = null, $timestampEndParam = null)
    {
        return $this->tripPaymentsRepository->findTripPaymentsToBePayedAndWrong($customer, $timestampEndParam);
    }

    public function getTripPaymentsForUserPayment(Customers $customer)
    {
        return $this->tripPaymentsRepository->findTripPaymentsForUserPayment($customer);
    }

    /**
     * @param null $timestampEndParam
     * @param null $condition
     * @param null $limit
     * @return array
     */

    public function getTripPaymentsForPaymentDetails($timestampEndParam = null, $condition = null, $limit = null)
    {
        return $this->tripPaymentsRepository->getCountTripPaymentsForPayment($timestampEndParam, $condition, $limit);
    }

    /**
     * @param $start
     * @param $end
     * @param $condition
     * @param $limit
     * @return array
     */

    public function getWrongTripPaymentsDetails($start, $end, $condition = null, $limit = null)
    {
        return $this->tripPaymentsRepository->getCountWrongTripPayments($start, $end, $condition, $limit);
    }

    /**
     * @param Customers $customer
     * @return TripPayments | null
     */
    public function getFirstTripPaymentNotPayedByCustomer(Customers $customer, $timestampEndParam = null)
    {
        return $this->tripPaymentsRepository->findFirstTripPaymentNotPayedByCustomer($customer, $timestampEndParam);
    }

    /**
     * @param TripPayments $tripPayment
     */
    public function setTripPaymentPayed(TripPayments $tripPayment)
    {
        $tripPayment->setPayedCorrectly();

        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();
    }

    public function getExpiryDate(TripPayments $tripPayment)
    {
        $date = $tripPayment->getToBePayedFrom();
        $date->add(new \DateInterval('P7D'));
        return $date;
    }

    public function setWrongPaymentsAsToBePayed(Customers $customer)
    {
        $command = new SetCustomerWrongPaymentsAsToBePayed($this->entityManager, $customer);

        return $command();
    }

    /**
     * @param Trips $trip
     * @return TripPayments[]
     */
    public function getByTrip(Trips $trip)
    {
        return $this->tripPaymentsRepository->findByTrip($trip);
    }

    /**
     * @param Customers $customer
     * @return TripPayments[]
     */
    public function getFailedByCustomer(Customers $customer)
    {
        return $this->tripPaymentsRepository->findFailedByCustomer($customer);
    }

    public function setExtraFare(Trips $trip, $extraFareAmount)
    {
         $tripPayment = $this->tripPaymentsRepository->findTripPaymentForTrip($trip);
         if(isset($tripPayment))
         {// if trip payment exist, add extra fare
            $totalCost = $tripPayment->getTotalCost();
            $tripPayment->setTotalCost($totalCost + $extraFareAmount);
         } else {   // else, trip payments dosn't exist, create a new
            $fare = $this->faresService->getFare();
            $tripPayment = new TripPayments($trip, $fare, 0, 0 ,0, $extraFareAmount); 
         }

        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentsRepository;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Trips;

class TripPaymentsService
{
    /**
     * @var TripPayments
     */
    private $tripPaymentsRepository;

    /**
     * @param TripPayments
     */
    public function __construct(TripPaymentsRepository $tripPaymentsRepository)
    {
        $this->tripPaymentsRepository = $tripPaymentsRepository;
    }

    /**
     * @return [[[TripPayments]]]
     */
    public function getTripPaymentsNoInvoiceGrouped()
    {
        return $this->groupTripPayments($this->tripPaymentsRepository->findTripPaymentsNoInvoice());
    }

    /**
     * @param [TripPayments] $tripPayments
     * @return [[[TripPayments]]]
     */
    private function groupTripPayments($tripPayments)
    {
        // group by date and customer
        $orderedTripPayments = [];
        foreach ($tripPayments as $tripPayment) {
            // retrieve date and customerId from tripPayment
            $date = $tripPayment->getPayedSuccessfullyAt()->format('Y-m-d');
            $customerId = $tripPayment->getTrip()->getCustomer()->getId();
            // if first tripPayment for that day, create the entry
            if (isset($orderedTripPayments[$date])) {
                // if first tripPayment for that customer, create the entry
                if (!isset($orderedTripPayments[$date][$customerId])) {
                    $orderedTripPayments[$date][$customerId] = [];
                }
            } else {
                $orderedTripPayments[$date] = [$customerId => []];
            }
            // add the tripPayment in the correct group
            array_push($orderedTripPayments[$date][$customerId], $tripPayment);
        }

        return $orderedTripPayments;
    }

    /**
     * @param Trips $trip
     * @return TripPayments || null
     */
    public function getTripPaymentByTrip(Trips $trip)
    {
        return $this->tripPaymentsRepository->findOneByTrip($trip);
    }
}

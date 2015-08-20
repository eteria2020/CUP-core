<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentsRepository;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Service\DatatableService;
use SharengoCore\Entity\Trips;

class TripPaymentsService
{
    /**
     * @var TripPayments
     */
    private $tripPaymentsRepository;

    /**
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @param TripPayments
     */
    public function __construct(
        TripPaymentsRepository $tripPaymentsRepository,
        DatatableService $datatableService
    ) {
        $this->tripPaymentsRepository = $tripPaymentsRepository;
        $this->datatableService = $datatableService;
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
     * retrieved the data for the datatable in the admin area
     */
    public function getFailedPaymentsData(array $filters)
    {
        $payments = $this->datatableService->getData('TripPayments', $filters);

        return array_map(function (TripPayments $payment) {
            return [
                'e' => [
                    'createdAt' => $payment->getCreatedAt()->format('Y-m-d H:i:s'),
                    'tripMinutes' => $payment->getTripMinutes(),
                    'parkingMinutes' => $payment->getParkingMinutes(),
                    'discountPercentage' => $payment->getDiscountPercentage(),
                    'totalCost' => $payment->getTotalCost()
                ],
                'cu' => [
                    'name' => $payment->getTrip()->getCustomer()->getName(),
                    'surname' => $payment->getTrip()->getCustomer()->getSurname()
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
     * @param int $id
     * @return TripPayments
     */
    public function getTripPaymentById($id)
    {
        return $this->tripPaymentsRepository->findOneById($id);
    }
}

<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentsRepository;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Service\DatatableService;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Commands\SetCustomerWrongPaymentsAsToBePayed;

use Doctrine\ORM\EntityManager;

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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param TripPayments
     */
    public function __construct(
        TripPaymentsRepository $tripPaymentsRepository,
        DatatableService $datatableService,
        EntityManager $entityManager
    ) {
        $this->tripPaymentsRepository = $tripPaymentsRepository;
        $this->datatableService = $datatableService;
        $this->entityManager = $entityManager;
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

        // sort payments according to their date
        ksort($orderedTripPayments);

        return $orderedTripPayments;
    }

    /**
     * retrieved the data for the datatable in the admin area
     */
    public function getFailedPaymentsData(array $filters)
    {
        $payments = $this->datatableService->getData('TripPayments', $filters);

        return array_map(function (TripPayments $payment) {
            $customer = $payment->getTrip()->getCustomer();
            return [
                'e' => [
                    'firstPaymentTryTs' => $payment->getFirstPaymentTryTs()->format('Y-m-d H:i:s'),
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
                    'email' => $customer->getEmail()
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
     * @return PersistentCollection
     */
    public function getTripPaymentsForPayment()
    {
        return $this->tripPaymentsRepository->findTripPaymentsForPayment();
    }

    public function getTripPaymentsForUserPayment(Customers $customer)
    {
        return $this->tripPaymentsRepository->findTripPaymentsForUserPayment($customer);
    }

    /*
     * @param Customers $customer
     * @return TripPayments | null
     */
    public function getFirstTripPaymentNotPayedByCustomer(Customers $customer)
    {
        return $this->tripPaymentsRepository->findFirstTripPaymentNotPayedByCustomer($customer);
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
     * @return string[]
     */
    public function getAvailableMonths()
    {
        return $this->tripPaymentsRepository->findAvailableMonths();
    }

    /**
     * Returns incomes for last month's days
     * @param string $dateString
     * @return array[] in format day => [fleet => income]
     */
    public function getDailyIncomeForMonth($dateString)
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P1M');
        $start = date_create_from_format('m-Y-d H:i:s', $dateString . '-01 00:00:00');
        $end = clone($start);
        $end->add($interval);
        $incomes = $this->tripPaymentsRepository->findPayedBetween(
            $start,
            $end,
            'DD-MM-YYYY'
        );
        return $this->groupIncomesByDate($incomes);
    }

    /**
     * Returns incomes for last month's weeks
     * @return array[] in format week => [fleet => income]
     */
    public function getWeeklyIncome()
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P28D');
        $end = date_create('next monday midnight');
        $start = clone($end);
        $start->sub($interval);
        $incomes = $this->tripPaymentsRepository->findPayedBetween(
            $start,
            $end,
            'YYYY-IW'
        );
        return $this->groupIncomesByDate($incomes);
    }

    /**
     * Returns incomes for last year's months
     * @return array[] in format month => [fleet => income]
     */
    public function getMonthlyIncome()
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P12M');
        $end = date_create('first day of next month midnight');
        $start = clone($end);
        $start->sub($interval);
        $incomes = $this->tripPaymentsRepository->findPayedBetween(
            $start,
            $end,
            'YYYY-MM'
        );
        return $this->groupIncomesByDate($incomes);
    }

    /**
     * @param array[] $incomes
     * @return array[]
     */
    private function groupIncomesByDate($incomes)
    {
        $groupedIncomes = [];
        foreach ($incomes as $income) {
            $tpDate = $income['tp_date'];
            if (!array_key_exists($tpDate, $groupedIncomes)) {
                $groupedIncomes[$tpDate] = [];
            }
            $groupedIncomes[$tpDate][$income['f_name']] = $income['tp_amount'];
        }
        return $groupedIncomes;
    }
}

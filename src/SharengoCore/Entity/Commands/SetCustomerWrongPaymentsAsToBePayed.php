<?php

namespace SharengoCore\Entity\Commands;

use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Customers;

use Doctrine\ORM\EntityManagerInterface;

class SetCustomerWrongPaymentsAsToBePayed extends Command
{
    /**
     * @var Customers
     */
    private $customer;

    public function __construct(
        EntityManagerInterface $entityManager,
        Customers $customer
    ) {
        $this->customer = $customer;

        return parent::__construct($entityManager);
    }

    protected function dql()
    {
        return 'UPDATE \SharengoCore\Entity\TripPayments tp '.
            'SET tp.status = :status, '.
            'tp.toBePayedFrom = :date, '.
            'tp.firstPaymentTryTs IS NULL '.
            'WHERE tp.trip IN ('.
            'SELECT t FROM \SharengoCore\Entity\Trips t WHERE t.customer = :customer) '.
            'AND tp.status = :actualStatus';
    }

    protected function params()
    {
        return [
            'status' => TripPayments::STATUS_TO_BE_PAYED,
            'date' => date_create('midnight'),
            'customer' => $this->customer,
            'actualStatus' => TripPayments::STATUS_WRONG_PAYMENT
        ];
    }
}

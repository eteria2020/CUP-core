<?php

namespace SharengoCore\Exception;

use SharengoCore\Entity\TripPayments;

use Zend\Stdlib\Hydrator\ClassMethods;

class TripPaymentWithoutDateException extends \UnexpectedValueException
{
    public function __construct(TripPayments $tripPayment)
    {
        $hydrator = new ClassMethods();

        $this->message = 'Payed successfully at is not a date for trip payment ' .
            json_encode($hydrator->extract($tripPayment));
    }
}
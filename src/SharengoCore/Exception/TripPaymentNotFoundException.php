<?php

namespace SharengoCore\Exception;

class TripPaymentNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the trip payment';
}

<?php

namespace SharengoCore\Exception;

class AlreadySetFirstPaymentTryTsException extends \Exception
{
    protected $message = 'Cannot change the value of firstPaymentTryTs if already set. Call TripPayments::clearFirstPaymentTryTs() first if this is what you want.';
}

<?php

namespace SharengoCore\Exception;

class ExtraPaymentNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the extra payment';
}

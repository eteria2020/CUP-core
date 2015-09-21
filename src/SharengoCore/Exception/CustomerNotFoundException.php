<?php

namespace SharengoCore\Exception;

class CustomerNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the customer';
}

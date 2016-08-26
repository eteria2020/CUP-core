<?php

namespace SharengoCore\Exception;

class CustomerNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the customer.';

    public function __construct($info = null)
    {
        if ($info !== null) {
            $this->message .= ' ' . $info;
        }
    }
}

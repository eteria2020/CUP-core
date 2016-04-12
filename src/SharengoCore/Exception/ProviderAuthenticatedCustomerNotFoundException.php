<?php

namespace SharengoCore\Exception;

class ProviderAuthenticatedCustomerNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the customer';
}

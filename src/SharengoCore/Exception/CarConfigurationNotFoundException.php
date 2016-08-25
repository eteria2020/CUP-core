<?php

namespace SharengoCore\Exception;

class CarConfigurationNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the car configuration';
}

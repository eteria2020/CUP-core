<?php

namespace SharengoCore\Exception;

class TripNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the trip';
}

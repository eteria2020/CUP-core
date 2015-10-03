<?php

namespace SharengoCore\Exception;

class FleetNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the fleet';
}

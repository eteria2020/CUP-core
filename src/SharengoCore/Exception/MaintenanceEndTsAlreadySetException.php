<?php

namespace SharengoCore\Exception;

class MaintenanceEndTsAlreadySetException extends \LogicException
{
    protected $message = 'Cannot change the value of EndTs if already set.';
}

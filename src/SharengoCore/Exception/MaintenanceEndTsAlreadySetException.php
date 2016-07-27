<?php

namespace SharengoCore\Exception;

class MaintenanceEndTsAlreadySetException extends \LogicException
{
    protected $message = 'Cannot change the value of endTs if already set.';
}

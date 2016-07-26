<?php

namespace SharengoCore\Exception;

class MaintenanceEndWebuserAlreadySetException extends \LogicException
{
    protected $message = 'Cannot change the value of endWebuser if already set.';
}

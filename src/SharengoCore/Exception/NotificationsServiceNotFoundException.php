<?php

namespace SharengoCore\Exception;

// Externals
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class NotificationsServiceNotFoundException extends ServiceNotCreatedException
{
    protected $message = 'Unable to create the required ServiceClass.';
}

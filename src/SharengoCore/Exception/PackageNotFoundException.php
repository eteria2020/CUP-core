<?php

namespace SharengoCore\Exception;

class PackageNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the customer bonus package';
}

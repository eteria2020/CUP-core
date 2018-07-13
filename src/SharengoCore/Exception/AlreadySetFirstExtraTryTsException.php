<?php

namespace SharengoCore\Exception;

class AlreadySetFirstExtraTryTsException extends \Exception
{
    protected $message = 'Cannot change the value of firstExtraTryTs if already set.';
}

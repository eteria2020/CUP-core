<?php

namespace SharengoCore\Exception;

class InvalidAuthorityCodeException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve an authority with the provided code';
}

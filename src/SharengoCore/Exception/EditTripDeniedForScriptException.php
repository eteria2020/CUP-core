<?php

namespace SharengoCore\Exception;

class EditTripDeniedForScriptException extends \DomainException
{
    protected $message = 'EditTripDeniedForTripException: trip cannot be edited! The payment script is running.';
}

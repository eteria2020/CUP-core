<?php

namespace Application\Exception;

class EditTripDeniedException extends \DomainException
{
    protected $message = 'EditTripDeniedException: trip cannot be edited! Either not ended or payment has begun.';
}

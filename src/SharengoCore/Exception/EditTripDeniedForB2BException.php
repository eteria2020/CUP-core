<?php

namespace SharengoCore\Exception;

class EditTripDeniedForB2BException extends \DomainException
{
    protected $message = 'EditTripDeniedForB2BException: trip cannot be edited! It\'s a business trip.';
}

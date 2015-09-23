<?php

namespace SharengoCore\Exception;

class EditTripWrongDateException extends \DomainException
{
    protected $message = 'EditTripWrongDateException: $endDate must not be prior to the timestampBeginning of the trip.';
}

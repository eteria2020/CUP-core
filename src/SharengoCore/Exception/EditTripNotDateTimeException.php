<?php

namespace SharengoCore\Exception;

class EditTripNotDateTimeException extends \DomainException
{
    protected $message = 'EditTripNotDateTimeException: $endDate must be null or \DateTime.';
}

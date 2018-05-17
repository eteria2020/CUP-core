<?php

namespace SharengoCore\Exception;

class EditTripExceed24HoursException extends \DomainException
{
    protected $message = 'EditTripExceed24HoursException: interval between $starDate and $endDate must not exceed 24 hours.';
}

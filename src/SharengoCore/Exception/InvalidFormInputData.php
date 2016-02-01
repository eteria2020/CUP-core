<?php

namespace SharengoCore\Exception;

class InvalidFormInputData extends \DomainException
{
    protected $message = 'The data provided is not valid';
}

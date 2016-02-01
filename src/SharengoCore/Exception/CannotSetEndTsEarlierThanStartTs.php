<?php

namespace SharengoCore\Exception;

class CannotSetEndTsEarlierThanStartTs extends \UnexpectedValueException
{
    protected $message = 'endTs cannot be set to a value that is before startTs';
}

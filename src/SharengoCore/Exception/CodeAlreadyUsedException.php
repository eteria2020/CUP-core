<?php

namespace SharengoCore\Exception;

class CodeAlreadyUsedException extends \InvalidArgumentException
{
    protected $message = 'The code has already been used.';
}

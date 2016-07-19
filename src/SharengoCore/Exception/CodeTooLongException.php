<?php

namespace SharengoCore\Exception;

class CodeTooLongException extends \InvalidArgumentException
{
    protected $message = 'The code is too long. 24 characters max.';

    /**
     * @param integer|null $length
     */
    public function __construct($length = null)
    {
        if ($length !== null) {
            $this->message .= ' Provided length is: ' . $length;
        }
    }
}

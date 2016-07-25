<?php

namespace SharengoCore\Exception;

class CodeTooLongException extends \InvalidArgumentException
{
    protected $message = 'The code is too long.';

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

<?php

namespace SharengoCore\Exception;

class NotAValidCodeException extends \InvalidArgumentException
{
    protected $message = 'The code is not in the required format.';

    /**
     * @param string|null $text
     */
    public function __construct($text = null)
    {
        if ($text !== null) {
            $this->message .= ' ' . $text;
        }
    }
}

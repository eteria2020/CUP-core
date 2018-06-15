<?php

namespace SharengoCore\Exception;

class PromoCodeOnceNotFound extends \InvalidArgumentException
{
    protected $message = 'The promo code once not found.';

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

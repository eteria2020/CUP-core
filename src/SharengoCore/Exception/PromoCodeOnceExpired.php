<?php

namespace SharengoCore\Exception;

class PromoCodeOnceExpired extends \InvalidArgumentException
{
    protected $message = 'The promo code once is expired.';

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

<?php

namespace SharengoCore\Exception;

class PromoCodeOnceNotActive extends \InvalidArgumentException
{
    protected $message = 'The promo code once not active.';

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

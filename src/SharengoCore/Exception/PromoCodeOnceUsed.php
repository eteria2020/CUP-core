<?php

namespace SharengoCore\Exception;

class PromoCodeOnceUsed extends \InvalidArgumentException
{
    protected $message = 'The promo code once already used.';

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

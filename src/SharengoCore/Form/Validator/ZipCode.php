<?php

namespace SharengoCore\Form\Validator;

use Zend\Validator\AbstractValidator;

class ZipCode extends AbstractValidator
{
    const INVALID = 'IdNumber';

    protected $messageTemplates = [
        self::INVALID => "Codice Avviamento Postale non corretto"
    ];

    public function isValid($value)
    {
        $translator = new \Zend\I18n\Translator\Translator();
        $messageTemplates[ self::INVALID] = $translator->translate("Codice Avviamento Postale non corretto");

        $value = strtoupper($value);
        $this->setValue($value);

        if (!preg_match("/^([0-9]{5})$/i", $value)) {
            $this->error(self::INVALID);
            return false;
        }

        return true;
    }
}

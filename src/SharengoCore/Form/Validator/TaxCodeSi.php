<?php

namespace SharengoCore\Form\Validator;

use Zend\Validator\AbstractValidator;

/**
 * Class TaxCodeSi
 * @package SharengoCore\Form\Validator
 *
 * Validator for Tax code for Slovenian instance (si_SL)
 *
 * Accepts an iterable of at least 12 digits and returns the number
 * as a 13 digit string with a valid 13th control digit.
 * Details about computation in
 * http://www.uradni-list.si/1/objava.jsp?urlid=19998&stevilka=345
 *
 */
class TaxCodeSi extends AbstractValidator
{
    const INVALID = 'TaxCodeSiInvalid';

    protected $messageTemplates = [
        self::INVALID => "Il numero identificativo non è corretto"
    ];

    public function isValid($value)
    {
        $result = false;
        $translator = new \Zend\I18n\Translator\Translator();
        $messageTemplates[ self::INVALID] = $translator->translate("Il numero identificativo non è corretto");

        $this->setValue($value);

        if (preg_match("/^([0-9]{13})$/i", $value)) {
            $result = $this->isValidPersonalIdentificationNumber($value);
        }

        if(!$result) {
            $this->error(self::INVALID);
        }

        return $result;
    }

    private function isValidPersonalIdentificationNumber ($value) {
        $result = false;
        $arrayFactor = array (7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2);

        $sum=0;
        for ($i = 0; $i < count($arrayFactor); $i++) {
            $sum += ((int)$value[$i]) * $arrayFactor[$i];
        }

        $checkSum = 11 - ($sum % 11);
        if($checkSum==$value[12]) {
            $result = true;
        }

        return $result;
    }
}

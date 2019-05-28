<?php

namespace SharengoCore\Form\Validator;

use EmailChecker\EmailChecker;
use Zend\Validator\AbstractValidator;


class DisposableEmail extends AbstractValidator
{
    const DISPOSABLE = 'Disposable';

    protected $messageTemplates = [
        self::DISPOSABLE => "Indirizzo email non accettato"
    ];

    public function __construct()
    {
        parent::__construct();
        $translator = new \Zend\I18n\Translator\Translator();
        $messageTemplates[ self::DISPOSABLE] = $translator->translate("Indirizzo email non accettato");
    }


    public function isValid($value) {

        if(substr_compare($value, '@qq.com', -strlen('@qq.com')) === 0) {   // qq.com is an exception
            return true;
        }

        $checker = new EmailChecker();
        if(!$checker->isValid($value)) {
            $this->error(self::DISPOSABLE);
            return false;
        } else {
            return true;
        }
    }
}
<?php

namespace SharengoCore\Form\Validator;

use Zend\Validator\AbstractValidator;

class PromoCode extends AbstractValidator
{
    const WRONG = 'promoCode';

    /**
     * @var \Zend\Authentication\AuthenticationService
     */
    private $promoCodesService;

    protected $messageTemplates = [
        self::WRONG => "Il codice inserito non è valido"
    ];

    public function __construct($options)
    {
        parent::__construct();
        $this->promoCodesService = $options['promoCodesService'];
    }

    public function isValid($value)
    {
        $this->setValue($value);

        if (!$this->promoCodesService->isValid($value)) {
            $this->error(self::WRONG);
            return false;
        }

        return true;
    }
}
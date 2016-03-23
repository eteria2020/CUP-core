<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mail\Transport\Sendmail;

class EmailServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //$emailTransport = new Sendmail();
        $emailSettings = $serviceLocator->get('Configuration')['emailSettings'];

        return new EmailService($emailTransport, $emailSettings);
    }
}

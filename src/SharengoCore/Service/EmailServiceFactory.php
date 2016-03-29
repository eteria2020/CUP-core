<?php

namespace SharengoCore\Service;

use Zend\Mail\Transport\Factory;
use Zend\Mail\Transport\File;
use Zend\Mail\Transport\FileOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EmailServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Configuration');
        $transportConfig = $config['emailTransport'];
        $emailSettings = $config['emailSettings'];

        $emailTransport = Factory::create($transportConfig);

        return new EmailService($emailTransport, $emailSettings);
    }
}

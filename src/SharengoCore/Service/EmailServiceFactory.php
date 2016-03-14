<?php

namespace SharengoCore\Service;

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

        $emailTransport = new $transportConfig['type'];

        if ($emailTransport instanceof File) {
            $options   = new FileOptions(array(
                'path' => $transportConfig['filePath'],
                'callback' => function (File $transport) {
                    return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
                },
            ));
            $emailTransport->setOptions($options);
        }
        return new EmailService($emailTransport, $emailSettings);
    }
}

<?php

namespace SharengoCore\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UploadedDriversLicenseMailSenderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');
        $notifyTo = $serviceLocator->get('Config')['driversLicense']['notifyTo'];

        return new UploadedDriversLicenseMailSender($emailService, $notifyTo);
    }
}

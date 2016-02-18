<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Filter\File\RenameUpload;

class ForeignDriversLicenseServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $renameUpload = new RenameUpload([
            'use_upload_extension' => true,
            'overwrite' => true
        ]);
        $config = $serviceLocator->get('Config');
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $eventManager = new EventManager('ForeignDriversLicenseService');

        return new ForeignDriversLicenseService(
            $renameUpload,
            $config['driversLicense'],
            $entityManager,
            $eventManager
        );
    }
}

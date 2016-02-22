<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Filter\File\RenameUpload;
use Zend\EventManager\EventManager;

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
        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');

        return new ForeignDriversLicenseService(
            $renameUpload,
            $config['driversLicense'],
            $entityManager,
            $eventManager,
            $datatableService
        );
    }
}

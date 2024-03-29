<?php

namespace SharengoCore\Service;

// Externals
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

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Customers(
                new DatatableQueryBuilders\Basic()
            )
        );

        return new ForeignDriversLicenseService(
            $renameUpload,
            $config['driversLicense'],
            $entityManager,
            $eventManager,
            $datatableService
        );
    }
}

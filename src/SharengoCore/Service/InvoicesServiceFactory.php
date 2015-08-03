<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InvoicesServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $invoicesRepository = $entityManager->getRepository('\SharengoCore\Entity\Invoices');
        $config = $serviceLocator->get('Config');
        $invoicesConfig = $config['invoice'];

        $datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Basic()
        );

        return new InvoicesService($invoicesRepository, $invoicesConfig, $datatableService);
    }
}

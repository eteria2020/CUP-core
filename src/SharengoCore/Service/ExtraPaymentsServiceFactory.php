<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExtraPaymentsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $invoicesService = $serviceLocator->get('SharengoCore\Service\Invoices');
        $extraPaymentsRepository = $entityManager->getRepository('\SharengoCore\Entity\ExtraPayments');
        $extraPaymentTriesService = $serviceLocator->get('SharengoCore\Service\ExtraPaymentTriesService');
        $customersService = $serviceLocator->get('SharengoCore\Service\CustomersService');
        $deactivationService = $serviceLocator->get('SharengoCore\Service\CustomerDeactivationService');
        
        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Customers(
                new DatatableQueryBuilders\Basic()
            ), 'cu'
        );



        return new ExtraPaymentsService($entityManager, $invoicesService, $datatableService, $extraPaymentsRepository, $extraPaymentTriesService, $customersService, $deactivationService);
    }
}

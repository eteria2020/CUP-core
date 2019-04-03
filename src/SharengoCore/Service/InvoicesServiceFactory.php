<?php

namespace SharengoCore\Service;

// Externals
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

        $serverInstance = null;

        if(isset($config['serverInstance'])) {
            $serverInstance = $config['serverInstance'];
        }
        // default subscription amount equals the first payment amount defined
        // for cartasi
        $invoicesConfig['subscription_amount'] = $config['cartasi']['first_payment_amount'];
        $simpleLoggerService = $serviceLocator->get('\SharengoCore\Service\SimpleLoggerService');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');
        
        $extraPaymentsRepository = $entityManager->getRepository('SharengoCore\Entity\ExtraPayments');

        // decorate the query builder with the needed decorators
        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Fleets(
                new DatatableQueryBuilders\Customers(
                    new DatatableQueryBuilders\Basic()
                )
            )
        );

        return new InvoicesService(
            $invoicesRepository,
            $datatableService,
            $entityManager,
            $simpleLoggerService,
            $invoicesConfig,
            $extraPaymentsRepository,
            $serverInstance
        );
    }
}

<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use SharengoCore\Service\DatatableQueryBuilders;

class TripsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $tripRepository = $entityManager->getRepository('\SharengoCore\Entity\Trips');
        $bonusRepository = $entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $bonusService = $entityManager->get('SharengoCore\Service\BonusService');
        $I_datatableService = $serviceLocator->get('SharengoCore\Service\DatatableService');
        $I_urlHelper = $serviceLocator->get('viewhelpermanager')->get('url');

        // decorate the query builder with the needed decorators
        $I_datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Cars(
                new DatatableQueryBuilders\Cards(
                    new DatatableQueryBuilders\Customers(
                        $I_datatableService->getQueryBuilder()
                    ),
                    'cu'
                )
            )
        );

        return new TripsService(
            $entityManager,
            $tripRepository,
            $bonusRepository,
            $bonusService,
            $I_datatableService,
            $I_urlHelper
        );
    }
}

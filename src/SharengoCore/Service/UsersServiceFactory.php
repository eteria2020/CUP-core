<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UsersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $options = $serviceLocator->get('zfcuser_module_options');
        $userRepository = $entityManager->getRepository('\SharengoCore\Entity\Webuser');

        /** @var DatatableServiceInterface **/
        $datatableService = $serviceLocator->get('SharengoCore\Service\SessionDatatableService');

        $datatableService->setQueryBuilder(
            new DatatableQueryBuilders\Basic()
        );

        return new UsersService(
            $entityManager,
            $options,
            $userRepository,
            $datatableService
        );
    }
}

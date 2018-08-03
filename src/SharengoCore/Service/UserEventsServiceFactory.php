<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserEventsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $userEventsRepository = $entityManager->getRepository('\SharengoCore\Entity\UserEvents');
        $webUserRepository = $entityManager->getRepository('\SharengoCore\Entity\Webuser');

//        $userEventsRepository = $entityManager->getRepository('\SharengoCore\Entity\UserEvents');

        return new UserEventsService(
            $entityManager,
            $userEventsRepository,
            $webUserRepository
        );
    }
}

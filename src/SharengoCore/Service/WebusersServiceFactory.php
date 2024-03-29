<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManager;

class WebusersServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $webusersRepository = $entityManager->getRepository('\SharengoCore\Entity\Webuser');

        return new WebusersService(
            $entityManager,
            $webusersRepository
        );
    }

}

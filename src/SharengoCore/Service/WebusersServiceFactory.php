<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WebusersServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        
        $webusersRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Webuser');

        return new WebusersService(
            $entityManager, $webusersRepository
        );
    }
}

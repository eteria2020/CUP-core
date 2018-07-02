<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OldCustomerDiscountsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $emailService = $serviceLocator->get('SharengoCore\Service\EmailService');
        $urlHelper = $serviceLocator->get('viewhelpermanager')->get('url');
        $translator = $serviceLocator->get('Translator');
        $host = $serviceLocator->get('config')['website']['uri'];
        $oldCustomerDiscountsRepository = $entityManager->getRepository('\SharengoCore\Entity\OldCustomerDiscount');

        return new OldCustomerDiscountsService(
            $entityManager,
            $emailService,
            $urlHelper,
            $translator,
            $host,
            $oldCustomerDiscountsRepository
        );
    }
}

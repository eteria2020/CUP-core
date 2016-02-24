<?php

namespace SharengoCore\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigurationsServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ConfigurationsService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Dependencies are fetched from Service Manager
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $configurationsRepository = $entityManager->getRepository('\SharengoCore\Entity\Configurations');

        $languageService = $serviceLocator->get('LanguageService');
        $translator = $languageService->getTranslator();

        return new ConfigurationsService($entityManager, $configurationsRepository, $translator);
    }
}
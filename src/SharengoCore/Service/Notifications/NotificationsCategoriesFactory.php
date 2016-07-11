<?php

namespace Application\Utility\CarsConfigurations;

// Internals
use SharengoCore\Entity\CarsConfigurations;
use Application\Utility\CarsConfigurations\Types\GenericCarConfiguration;
// Externals
use Zend\Mvc\I18n\Translator;

class NotificationsCategoriesFactory
{
    /**
     * Create specific Notifications Category Utility Class
     *
     * @param string $configType
     * @param mixed $configValue
     * @param Translator $translator
     *
     * @return NotificationsCategoriesFactory
     */
    public static function create(
        $configType,
        $configValue,
        Translator $translator
    ) {
        $notificationCategory = 'Application\\Utility\\CarsConfigurations\\Types\\'.$configType;

        if (class_exists($notificationCategory)) {
            return new $notificationCategory(
               
            );
        }

        return new GenericCarConfiguration(
            $configValue,
            $translator
        );
    }

    /**
     * Create specific CarsConfigurations Utility Class from Form
     *
     * @param CarsConfigurations $carConfiguration
     * @param Translator $translator
     *
     * @return CarsConfigurationsTypesInterface
     */
    public static function createFromCarConfiguration(
        CarsConfigurations $carConfiguration,
        Translator $translator
    ) {
        return self::create(
            $carConfiguration->getKey(),
            $carConfiguration->getValue(),
            $translator
        );
    }
}

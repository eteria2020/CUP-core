<?php

namespace SharengoCore;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {

        $em = $e->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');
        $platform = $em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('gender', 'string');
        $platform->registerDoctrineTypeMapping('car_status', 'string');
        $platform->registerDoctrineTypeMapping('cleanliness', 'string');
        $platform->registerDoctrineTypeMapping('_text', 'string');
        $platform->registerDoctrineTypeMapping('_int4', 'string');
        $platform->registerDoctrineTypeMapping('geometry', 'string');
        $platform->registerDoctrineTypeMapping('jsonb', 'string');
        $platform->registerDoctrineTypeMapping('reservations_archive_reason', 'string');
        $platform->registerDoctrineTypeMapping('invoice_type', 'string');
        $platform->registerDoctrineTypeMapping('trip_payment_status', 'string');
        $platform->registerDoctrineTypeMapping('polygon', 'string');
        $platform->registerDoctrineTypeMapping('extra_payments_types', 'string');
        $platform->registerDoctrineTypeMapping('disabled_reason', 'string');
        $platform->registerDoctrineTypeMapping('reactivation_reason', 'string');
        $platform->registerDoctrineTypeMapping('csv_anomaly_type', 'string');

        $this->registerEventListeners(
            $e->getApplication()->getEventManager(),
            $e->getApplication()->getServiceManager()
        );
    }

    /**
     * attaches the event listeners of the module
     *
     * @param EventManagerInterface $events
     */
    private function registerEventListeners(
        EventManagerInterface $events,
        ServiceLocatorInterface $serviceLocator
    ) {
        $disableContractsListener = $serviceLocator->get('SharengoCore\Listener\DisableContractListener');

        $events->getSharedManager()->attachAggregate($disableContractsListener);
    }

    /**
     * Returns autoloader configuration
     * @return multitype:multitype:multitype:string
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
}

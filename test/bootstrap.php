<?php

namespace SharengoCore;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;

class Bootstrap
{
    protected static $serviceManager;

    public static function init()
    {
        $modulePath = static::findParentPath('module');
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            throw new RuntimeException('Cannot locate autoload.php');
        }

        $config = [
            'modules' => [
                'SharengoCore',
                'Cartasi'
            ],
            'module_listener_options' => [
                'module_paths' => [
                    $modulePath,
                    $vendorPath
                ]
            ]
        ];

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }
}

Bootstrap::init();

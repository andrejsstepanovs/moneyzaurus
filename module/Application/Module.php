<?php

namespace Application;

use Varient\Database\Helper\TableLoader;
use Zend\ServiceManager\ServiceManager;
use Application\Helper\Purchase as PurchaseHelper;
use Varient\Database\ActiveRecord\ActiveRecord;

class Module
{
    /**
     * @param Zend\Mvc\MvcEvent $mvcEvent
     */
//    public function onBootstrap($mvcEvent)
//    {
//        /** @var $application \Zend\Mvc\Application */
//        $application = $mvcEvent->getApplication();
//
//        /** @var $serviceManager \Zend\ServiceManager\ServiceManager */
//        $serviceManager = $application->getServiceManager();
//    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        $config = array_merge(
            include __DIR__ . '/config/router.config.php',
            include __DIR__ . '/config/navigation.config.php',
            include __DIR__ . '/config/view.config.php',
            include __DIR__ . '/config/translator.config.php',
            include __DIR__ . '/config/controllers.config.php',
            include __DIR__ . '/config/service_manager.config.php',
            include __DIR__ . '/config/di.config.php'
        );

        return $config;
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ActiveRecord' => function(ServiceManager $sm) {
                    $activeRecord = new ActiveRecord();
                    return $activeRecord->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
                }
            ),
        );
    }

}

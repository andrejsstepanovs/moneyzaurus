<?php

namespace Application;

use Varient\Database\Helper\TableLoader;
use Zend\ServiceManager\ServiceManager;
use Application\Helper\Purchase as PurchaseHelper;

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
            include __DIR__ . '/config/service_manager.config.php'
        );

        return $config;
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Varient\Database\Helper\TableLoader' => function(ServiceManager $sm) {
                    return new TableLoader(
                        $sm->get('Zend\Db\Adapter\Adapter'),
                        __NAMESPACE__ . '\Table',
                        __NAMESPACE__ . '\Model'
                    );
                },
                'Application\Table\Item' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('Item');
                },
                'Application\Table\User' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('User');
                },
                'Application\Table\Transaction' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('Transaction');
                },
                'Application\Table\Group' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('Group');
                },
                'Application\Table\Currency' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('Currency');
                },
                'Application\Table\Connection' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('Connection');
                },
                'Application\Table\Purchase' => function(ServiceManager $sm) {
                    return $sm->get('Varient\Database\Helper\TableLoader')
                              ->getTable('Purchase');
                },
                'Application\Helper\Purchase' => function(ServiceManager $sm) {
                    return new PurchaseHelper($sm);
                },
            ),
        );
    }

}

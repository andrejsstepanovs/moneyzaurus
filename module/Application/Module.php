<?php

namespace Application;

use Zend\Db\TableGateway\Feature;
use Zend\Mvc\MvcEvent;


class Module
{
    /** @var array */
    protected $config;


    /**
     * @param \Zend\Mvc\MvcEvent $mvcEvent
     */
    public function onBootstrap(MvcEvent $mvcEvent)
    {
        /** @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();

        /** @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $application->getServiceManager();

        Feature\GlobalAdapterFeature::setStaticAdapter(
            $serviceManager->get('Zend\Db\Adapter\Adapter')
        );


        /** @var $acl \Application\Acl\Acl */
        $acl = $serviceManager->get('Application\Acl\Acl');

        $application->getEventManager()
                    ->attach(
                         \Zend\Mvc\MvcEvent::EVENT_ROUTE,
                         array($acl, 'checkAcl'),
                         -100
                    );
    }

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

    /**
     * @return array
     */
    public function getConfig($key = null)
    {
        if (null === $this->config) {
            $this->config = array_merge(
                include __DIR__ . '/config/router.config.php',
                include __DIR__ . '/config/navigation.config.php',
                include __DIR__ . '/config/view.config.php',
                include __DIR__ . '/config/translator.config.php',
                include __DIR__ . '/config/controllers.config.php',
                include __DIR__ . '/config/service_manager.config.php',
                include __DIR__ . '/config/di.config.php',
                include __DIR__ . '/config/acl.config.php'
            );
        }

        if (!empty($key)) {
            return $this->config[$key];
        }

        return $this->config;
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'AuthService' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new \Zend\Authentication\Adapter\DbTable(
                            $dbAdapter, 'user', 'email', 'password', 'MD5(?)'
                    );

                    return $sm->get('Zend\Authentication\AuthenticationService')
                              ->setAdapter($dbTableAuthAdapter);
                },
                'Application\Acl\Acl' => function($sm) {
                    return new Acl\Acl($sm);
                },
            )
        );
    }


//    public function _getViewHelperConfig()
//    {
//        return array(
//            'factories' => array(
//                // This will overwrite the native navigation helper
//                'navigation' => function(\Zend\View\HelperPluginManager $pm) {
//
//
//                    $acl = $pm->getServiceLocator()->get('Application\Acl\Acl');
//
//                    $zendAcl = $acl->getAcl();
//
//                    \DEBUG::dump($zendAcl);
//
//                    $acl = $pm->getServiceLocator()->get('Application\Acl\Acl');
//                    \DEBUG::dump($acl);
//
//
//                    // Setup ACL:
//                    $acl = new \Zend\Permissions\Acl\Acl();
//                    $acl->addRole(new \Zend\Permissions\Acl\Role\GenericRole('member'));
//                    $acl->addRole(new \Zend\Permissions\Acl\Role\GenericRole('admin'));
//                    $acl->addResource(new \Zend\Permissions\Acl\Resource\GenericResource('mvc:admin'));
//                    $acl->addResource(new \Zend\Permissions\Acl\Resource\GenericResource('mvc:community.account'));
//                    $acl->allow('member', 'mvc:community.account');
//                    $acl->allow('admin', null);
//
//                    // Get an instance of the proxy helper
//                    $navigation = $pm->get('Zend\View\Helper\Navigation');
//
//                    // Store ACL and role in the proxy helper:
//                    $navigation->setAcl($acl)
//                               ->setRole('member');
//
//                    // Return the new navigation helper instance
//                    return $navigation;
//                }
//            )
//        );
//    }
//
//    public function ___getViewHelperConfig()
//    {
//        return array(
//            'factories' => array(
//                'mynavigation' => function($sm) {
//                    $helper = new \Application\View\Helper\Navigation();
//                    return $helper;
//                }
//            )
//        );
//   }
}

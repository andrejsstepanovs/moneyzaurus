<?php

namespace Application;

use Zend\Db\TableGateway\Feature;
use Zend\Mvc\MvcEvent;

class Module
{
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


//        \DEBUG::dump($this->zfcUserAuthentication()->getIdentity());

        $acl = $serviceManager->get('Application\EventManager\Acl')
                              ->setMvcEvent($mvcEvent)
                              ->setAclConfig($this->getConfig('acl'));

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
            'invokables' => array(
                '\Application\EventManager\Acl' => '\Application\EventManager\Acl'
            ),
        );
    }
}

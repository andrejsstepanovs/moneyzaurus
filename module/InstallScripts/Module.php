<?php
namespace InstallScripts;


class Module
{
    /**
     * @param Zend\Mvc\MvcEvent $mvcEvent
     * @return void
     */
    public function onBootstrap($mvcEvent)
    {
        /** @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();

        /** @var $serviceManager \Zend\ServiceManager\ServiceManager */
        $serviceManager = $application->getServiceManager();

        /** @var $viewHelperManager \Zend\View\HelperPluginManager */
        $viewHelperManager = $serviceManager->get('ViewHelperManager');


        $adapter = $serviceManager->get('Zend\Db\Adapter\Adapter');



        $script = new InstallScript($adapter);
        //$script->special();
    }

    /**
     * @return array
     */
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
}

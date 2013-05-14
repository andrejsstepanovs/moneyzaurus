<?php
namespace jQueryMobileMenu;

/**
 * Module overwrite navigation html to match jQuery mobile menu attributes.
 *
 * Set navigation config with a_params array
 * 'navigation' => array(
 *     'default' => array(
 *         array(
 *             'label' => 'Labelname',
 *             'route' => 'routepath',
 *             'a_params' => array(
 *                 'data-transition' => 'slide',
 *                 'data-icon'       => 'plus',
 *             )
 *         )
 *     )
 * )
 */
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

        // overwrite \Zend\View\Helper\Navigation\Menu
        $pluginManager = $viewHelperManager->get('Navigation')->getPluginManager();
        $pluginManager->setInvokableClass('Menu', 'jQueryMobileMenu\View\Helper\Navigation\Menu');
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

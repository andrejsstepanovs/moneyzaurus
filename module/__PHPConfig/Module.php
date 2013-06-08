<?php
namespace PhpConfig;

/**
 * Allow to set php.ini values on the fly.
 */
class Module
{
    /**
     * @param Zend\Mvc\MvcEvent $mvcEvent
     */
    public function onBootstrap($mvcEvent)
    {
        /** @var $application \Zend\Mvc\Application */
        $application = $mvcEvent->getApplication();

        // set php.ini values
        $config = $application->getConfig();
        foreach ($config['phpSettings'] as $key => $value) {
            ini_set($key, $value);
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/phpconf.config.php';
    }
}

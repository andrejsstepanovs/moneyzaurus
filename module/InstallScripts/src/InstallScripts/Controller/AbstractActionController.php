<?php

namespace InstallScripts\Controller;

use Zend\Mvc\MvcEvent;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Request as ZendConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController as ZendActionController;
use InstallScripts\Storage as InstallScriptsStorage;
use InstallScripts\Locator as InstallScriptsLocator;
use InstallScripts\Exception\ActionControllerException;
use InstallScripts\Script;


class AbstractActionController extends ZendActionController
{
    /** @var array */
    protected $config;

    /** @var \InstallScripts\Model\Storage */
    protected $storage;

    /** @var \InstallScripts\Locator\Locator */
    protected $locator;

    /** @var \Zend\Console\Adapter\Posix */
    protected $console;


    /**
     * @param  \Zend\Mvc\MvcEvent $mvcEvent
     * @return mixed
     * @throws \Zend\Mvc\Exception\DomainException
     */
    public function onDispatch(MvcEvent $mvcEvent)
    {
        $request = $this->getRequest();

        if (!$request instanceof ZendConsoleRequest){
            throw new ActionControllerException(
                'You can only use this action from a console!'
            );
        }

        return parent::onDispatch($mvcEvent);
    }

    /**
     * @return \Zend\Console\Adapter\Posix
     */
    protected function getConsole()
    {
        if (null === $this->console) {
            $this->console = $this->getServiceLocator()->get('console');
        }

        return $this->console;
    }

    /**
     * @param string $message
     * @param integer $color
     * @param integer $strPad
     * @return string
     */
    protected function colorize($message, $color = Color::RESET, $strPad = null)
    {
        if ($strPad) {
            $message = str_pad($message, $strPad);
        }

        return $this->getConsole()->colorize($message, $color);
    }

    /**
     * @param string $title
     * @param integer $color
     * @return string
     */
    protected function getTitle($title, $color = Color::RED)
    {
        $console = $this->getConsole();

        $output = sprintf("%s\n%s\n%s\n",
            str_repeat('-', $console->getWidth()),
            $title,
            str_repeat('-', $console->getWidth())
        );

        return $console->colorize($output, $color);
    }

    /**
     * @return array
     * @throws ActionControllerException
     */
    protected function getInstallScriptsConfig()
    {
        $config = $this->getEvent()->getApplication()->getConfig();

        if (!array_key_exists('InstallScripts', $config)
            || !is_array($config['InstallScripts'])
        ) {
            throw new ActionControllerException(
                'InstallScripts config not found'
            );
        }

        return $config['InstallScripts'];
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getConfig($key = null)
    {
        if (null === $this->config) {
            $this->config = $this->getInstallScriptsConfig();
        }

        if (null !== $key) {
            if (!array_key_exists($key, $this->config)) {
                throw new ActionControllerException(
                    'InstallScripts config key '
                    . '"' . $key . '" not found'
                );
            }

            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @return \InstallScripts\StorageAdapter\StorageAdapterInterface
     */
    protected function getStorage()
    {
        if (null === $this->storage) {
            $this->storage = new InstallScriptsStorage();
            $this->storage->setConfig($this->getConfig());
        }

        return $this->storage;
    }

    /**
     * @return \InstallScripts\Locator
     */
    protected function getLocator()
    {
        $this->getServiceLocator();
        if (null === $this->locator) {
            $this->locator = new InstallScriptsLocator();
            $this->locator->setConfig($this->getConfig());
        }

        return $this->locator;
    }

    /**
     * @param \InstallScripts\Script $script
     * @param string $method
     * @return boolean
     * @throws ActionControllerException
     */
    protected function execute(Script $script, $method)
    {
        if (!method_exists($script, $method)) {
            throw new ActionControllerException(
                'Script "' . $script->getName() . '" '
                . 'have no method "' . $method . '"'
            );
        }

        if (!is_callable(array($script, $method))) {
            throw new ActionControllerException(
                'Script method "' . $script->getName()
                . '::' . $method . '()" ' . 'is not callable'
            );
        }

        $script->setMvcEvent($this->getEvent());

        $start = time();
        $startLabel = date('Y-m-d H:i:s', $start);

        echo $this->colorize('Start: ', Color::NORMAL, 12);
        echo $this->colorize($startLabel, Color::LIGHT_YELLOW, 20);
        echo PHP_EOL;


        $result = call_user_method($method, $script);


        $spentTime = gmdate('H:i:s', time() - $start);
        echo $this->colorize('Stop: ', Color::NORMAL, 12);
        echo $this->colorize(date('Y-m-d H:i:s'), Color::LIGHT_YELLOW, 20);
        echo $this->colorize($spentTime, Color::LIGHT_GREEN);
        echo PHP_EOL;


        return $result;
    }
}

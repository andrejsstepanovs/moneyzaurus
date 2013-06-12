<?php

namespace InstallScripts\Controller;

use Zend\Mvc\MvcEvent;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Request as ZendConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController as ZendActionController;
use InstallScripts\Storage as InstallScriptsStorage;
use InstallScripts\Locator as InstallScriptsLocator;
use InstallScripts\Exception\ActionControllerException;


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
     * @return string
     */
    protected function colorize($message, $color = Color::RESET)
    {
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

}

<?php

namespace InstallScripts\Controller;

use Zend\Mvc\Controller\AbstractActionController as Controller;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use InstallScripts\Storage\Storage as InstallScriptStorage;
use InstallScripts\Locator\Locator as InstallScriptLocator;
use Zend\Console\ColorInterface;


class AbstractActionController extends Controller
{
    /** @var array */
    protected $config;

    /** @var \InstallScripts\Model\Storage */
    protected $installScriptStorage;

    /** @var \InstallScripts\Locator\Locator */
    protected $installScriptLocator;

    /** @var \Zend\Console\Adapter\Posix */
    protected $console;


    /**
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest){
            throw new \RuntimeException('You can only use this action from a console!');
        }

        return parent::onDispatch($e);
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
    protected function colorize($message, $color = ColorInterface::RESET)
    {
        return $this->getConsole()->colorize($message, $color);
    }

    /**
     * @param string $title
     * @param integer $color
     * @return string
     */
    protected function getTitle($title, $color = ColorInterface::RED)
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
     * @param string $key
     * @return mixed
     */
    protected function getConfig($key = null)
    {
        /** @var $event \Zend\Mvc\MvcEvent */
        $event = $this->getEvent();

        if (null === $this->config) {
            $config = $event->getApplication()->getConfig();
            if (array_key_exists('InstallScripts', $config)
                && is_array($config['InstallScripts'])
            ) {
                $this->config = $config['InstallScripts'];
            }
        }

        if (!empty($key)) {
            return $this->config[$key];
        }

        return $this->config;
    }

    /**
     * @return \InstallScripts\Model\Storage
     */
    protected function getInstallScriptStorage()
    {
        if (null === $this->installScriptStorage) {
            $this->installScriptStorage = new InstallScriptStorage();
            $this->installScriptStorage->setConfig($this->getConfig());
        }

        return $this->installScriptStorage;
    }

    /**
     * @return \InstallScripts\Locator\Locator
     */
    protected function getInstallScriptLocator()
    {
        $this->getServiceLocator();
        if (null === $this->installScriptLocator) {
            $this->installScriptLocator = new InstallScriptLocator();
            $this->installScriptLocator->setConfig($this->getConfig());
        }

        return $this->installScriptLocator;
    }
}

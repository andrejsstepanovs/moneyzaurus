<?php

namespace InstallScripts\Model;

use Zend\Mvc\Controller\AbstractActionController as Controller;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;
use InstallScripts\Storage\Storage as InstallScriptStorage;
use InstallScripts\Model\Locator as InstallScriptLocator;


class AbstractActionController extends Controller
{
    /** @var array */
    protected $config;

    /** @var \InstallScripts\Model\Storage */
    protected $installScriptStorage;

    /** @var \InstallScripts\Model\Locator */
    protected $installScriptLocator;


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
     * @return \InstallScripts\Model\Locator
     */
    protected function getInstallScriptLocator()
    {
        if (null === $this->installScriptLocator) {
            $this->installScriptLocator = new InstallScriptLocator();
            $this->installScriptLocator->setConfig($this->getConfig());
        }

        return $this->installScriptLocator;
    }
}

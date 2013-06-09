<?php

namespace InstallScripts\Model;

use Zend\Mvc\Controller\AbstractActionController as Controller;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;


class AbstractActionController extends Controller
{
    /** @var array */
    protected $config;

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

}

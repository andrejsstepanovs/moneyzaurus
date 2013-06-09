<?php

namespace InstallScripts\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Console\Request as ConsoleRequest;


class IndexController extends AbstractActionController
{
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

    public function updateAction()
    {
        echo __METHOD__;
    }

    public function listAction()
    {
        echo __METHOD__;
    }

    public function installAction()
    {
        echo __METHOD__;
    }

    public function configAction()
    {
        echo __METHOD__;
    }

    public function setAction()
    {
        $request = $this->getRequest();

        $resource = $request->getParam('bundle');
        $version  = $request->getParam('version');


        echo __METHOD__;
    }

    public function setLatestAction()
    {
        echo __METHOD__;
    }

}

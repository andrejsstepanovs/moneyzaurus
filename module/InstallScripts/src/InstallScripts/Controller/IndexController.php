<?php

namespace InstallScripts\Controller;

use InstallScripts\Model\AbstractActionController;
use InstallScripts\Model\Storage as InstallScriptStorage;
use InstallScripts\Model\Locator as InstallScriptLocator;
use Zend\Debug\Debug as ZendDebug;


class IndexController extends AbstractActionController
{

    public function listAction()
    {
        $config = $this->getConfig();
        $locator = new InstallScriptLocator($config);

        $bundles = $locator->getBundles();
        foreach ($bundles as $bundle) {
            echo $bundle->getName();
        }
    }

    public function configAction()
    {
        $config = $this->getConfig();
        $storage = new InstallScriptStorage($config);

        $dump = new ZendDebug();
        $dump->dump($config, 'Config Data');
        $dump->dump($storage->load(), 'Storage Data');
    }

    public function updateAction()
    {
        echo __METHOD__;
    }

    public function installAction()
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

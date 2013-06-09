<?php

namespace InstallScripts\Controller;

use InstallScripts\Model\AbstractActionController;
use InstallScripts\Model\Storage as InstallScriptStorage;
use InstallScripts\Model\Locator as InstallScriptLocator;


class IndexController extends AbstractActionController
{

    public function updateAction()
    {
        echo __METHOD__;
    }

    public function listAction()
    {
        $config = $this->getConfig();
        $locator = new InstallScriptLocator($config);

        $bundles = $locator->getBundles();
        foreach ($bundles as $bundle) {
            echo $bundle->getName();
        }
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

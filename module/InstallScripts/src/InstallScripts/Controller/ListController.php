<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface;


class ListController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('list');

        $storage = $this->getInstallScriptStorage()->getAdapter();
        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {
            $bundleName = $bundle->getName();
            echo str_pad($storage->getBundleVersion($bundleName), 7);
            echo $this->colorize($bundleName, ColorInterface::CYAN) . PHP_EOL;
        }
    }

}

<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface;


class SetController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('set');

        $request = $this->getRequest();

        $bundleName = $request->getParam('bundle');
        $version    = $request->getParam('version');

        $storageAdapter = $this->getInstallScriptStorage()->getAdapter();

        $changed = false;

        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {
            $currentBundleName = str_replace('\\', '', $bundle->getName());
            $searchBundleName  = str_replace('\\', '', $bundleName);

            if ($currentBundleName != $searchBundleName) {
                continue;
            }

            $currentVersion = $storageAdapter->getBundleVersion($bundle->getName());

            if ($version != $currentVersion) {
                $storageAdapter->setBundleVersion($bundle->getName(), $version);

                echo  $this->colorize(str_pad($currentVersion, 7), ColorInterface::LIGHT_MAGENTA);
                echo ' => ';
                echo  $this->colorize(str_pad($version, 7), ColorInterface::LIGHT_CYAN);
                echo  $this->colorize($bundleName, ColorInterface::BLUE);
                echo PHP_EOL;

                $changed = true;
            } else {
                echo str_pad($currentVersion, 7);
                echo  $this->colorize('no changes  ', ColorInterface::MAGENTA);
                echo  $this->colorize($bundleName, ColorInterface::BLUE);
                echo PHP_EOL;
            }
        }

        if ($changed) {
            if ($storageAdapter->save()) {
                echo $this->colorize('Saved', ColorInterface::LIGHT_GREEN) .  PHP_EOL;
            } else {
                echo $this->colorize('Failed to save data', ColorInterface::RED) .  PHP_EOL;
            }
        } else {
            echo $this->colorize('Nothing to save', ColorInterface::GREEN) .  PHP_EOL;
        }
    }

}

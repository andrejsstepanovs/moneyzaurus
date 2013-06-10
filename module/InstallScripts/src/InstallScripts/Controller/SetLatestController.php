<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface;


class SetLatestController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('set-latest');

        $storageAdapter = $this->getInstallScriptStorage()->getAdapter();

        $changed = false;

        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {
            $currentVersion = $storageAdapter->getBundleVersion($bundle->getName());
            $maxVersion     = $bundle->getMaxVersion();
            $bundleName     = $bundle->getName();

            if (version_compare($currentVersion, $maxVersion) < 0) {
                $storageAdapter->setBundleVersion($bundleName, $maxVersion);

                echo str_pad($currentVersion, 7);
                echo ' => ';
                echo str_pad($maxVersion, 7);
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

<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use InstallScripts\Exception;
use Zend\Console\ColorInterface;


class InstallController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('install');

        $request = $this->getRequest();

        $bundleName     = $request->getParam('bundle');
        $installVersion = $request->getParam('version');

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

            $versions = $bundle->getVersionsSorted();
            if (!array_key_exists($installVersion, $versions)) {
                throw new Exception\BundleException(
                    'Bundle "' . $bundle->getName() . '" '
                    . 'have no version "' . $installVersion . '". '
                    . 'Available versions: ' . implode(' ; ', array_keys($versions))
                );
            }

            $method = $versions[$installVersion];

            if (!method_exists($bundle, $method)) {
                throw new Exception\BundleException(
                    'Bundle "' . $bundle->getName() . '" '
                    . 'have no method "' . $method . '"'
                );
            }

            if (!is_callable(array($bundle, $method))) {
                throw new Exception\BundleException(
                    'Bundle method "' . $bundle->getName() . '::' . $method . '()" '
                    . 'is not callable'
                );
            }


            $result = call_user_method($versions[$installVersion], $bundle);
            if ($result) {
                $storageAdapter->setBundleVersion($bundle->getName(), $installVersion);

                echo  $this->colorize(str_pad($currentVersion, 7), ColorInterface::LIGHT_MAGENTA);
                echo ' => ';
                echo  $this->colorize(str_pad($installVersion, 7), ColorInterface::LIGHT_CYAN);
                echo  $this->colorize($bundleName, ColorInterface::BLUE);
                echo PHP_EOL;

                $changed = true;
            } else {
                echo str_pad($currentVersion, 7);
                echo  $this->colorize('install failed ', ColorInterface::RED);
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

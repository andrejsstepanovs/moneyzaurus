<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use InstallScripts\Exception;
use Zend\Console\ColorInterface;


class UpdateController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('update');

        $storageAdapter = $this->getInstallScriptStorage()->getAdapter();

        $changed = false;

        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {
            $bundleName = $bundle->getName();

            $currentVersion = $storageAdapter->getBundleVersion($bundleName);

            $versions = $bundle->getVersionsSorted();

            foreach ($versions AS $installVersion => $method) {

                if (version_compare($currentVersion, $installVersion) >= 0) {
                    continue;
                }

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

                    echo str_pad($currentVersion, 7);
                    echo ' => ';
                    echo str_pad($installVersion, 7);
                    echo  $this->colorize($bundleName, ColorInterface::BLUE);
                    echo PHP_EOL;

                    $currentVersion = $installVersion;
                    $changed = true;
                } else {
                    echo str_pad($currentVersion, 7);
                    echo  $this->colorize('install failed ', ColorInterface::MAGENTA);
                    echo  $this->colorize($bundleName, ColorInterface::BLUE);
                    echo PHP_EOL;
                }
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

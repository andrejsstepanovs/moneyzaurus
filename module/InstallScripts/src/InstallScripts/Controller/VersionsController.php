<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use InstallScripts\Exception;
use Zend\Console\ColorInterface;


class VersionsController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('versions');

        $request = $this->getRequest();

        $bundleName     = $request->getParam('bundle');
        $storageAdapter = $this->getInstallScriptStorage()->getAdapter();

        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {

            if (!empty($bundleName)) {
                $currentBundleName = str_replace('\\', '', $bundle->getName());
                $searchBundleName  = str_replace('\\', '', $bundleName);

                if ($currentBundleName != $searchBundleName) {
                    continue;
                }
            }

            echo $this->getTitle($bundle->getName(), ColorInterface::CYAN);

            $currentVersion = $storageAdapter->getBundleVersion($bundle->getName());

            $versions = $bundle->getVersionsSorted();
            foreach ($versions as $version => $method) {
                $compare = version_compare($currentVersion, $version);
                if ($compare < 0) {
                    $versionLabel = $this->colorize('not installed', ColorInterface::RED);
                } elseif ($compare == 0) {
                    $versionLabel = $this->colorize('currently installed', ColorInterface::LIGHT_GREEN);
                } else {
                    $versionLabel = $this->colorize('installed', ColorInterface::GREEN);
                }
                echo str_pad($version, 7) . ' ' . str_pad($versionLabel, 50) . ' ' . $method . '()' . PHP_EOL;
            }
        }
    }

    public function updateAction()
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
                    echo $currentVersion . ' => ' . $installVersion . ' # ' . $bundleName . PHP_EOL;
                    $currentVersion = $installVersion;
                    $changed = true;
                } else {
                    echo $currentVersion . ' install failed # ' . $bundleName . PHP_EOL;
                }
            }
        }

        if ($changed) {
            if ($storageAdapter->save()) {
                echo 'Saved' .  PHP_EOL;
            } else {
                echo 'Failed to save data' .  PHP_EOL;
            }
        } else {
            echo 'Nothing to save' .  PHP_EOL;
        }
    }

}

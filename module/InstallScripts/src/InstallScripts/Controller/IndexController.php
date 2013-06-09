<?php

namespace InstallScripts\Controller;

use InstallScripts\Model\AbstractActionController;

use Zend\Debug\Debug as ZendDebug;


class IndexController extends AbstractActionController
{

    public function listAction()
    {
        $storage = $this->getInstallScriptStorage()->getAdapter();
        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {
            $bundleName = $bundle->getName();
            echo $storage->getBundleVersion($bundleName) . ' # '
                 . $bundleName . PHP_EOL;
        }
    }

    public function configAction()
    {
        $storage = $this->getInstallScriptStorage();
        $config  = $this->getConfig();

        $dump = new ZendDebug();
        $dump->dump($config, 'Config Data');
        $dump->dump($storage->getAdapter()->load(), 'Storage Data');
    }

    public function setLatestAction()
    {
        $storageAdapter = $this->getInstallScriptStorage()->getAdapter();

        $changed = false;

        $bundles = $this->getInstallScriptLocator()->getBundles();
        foreach ($bundles as $bundle) {
            $currentVersion = $storageAdapter->getBundleVersion($bundle->getName());
            $maxVersion     = $bundle->getMaxVersion();
            $bundleName     = $bundle->getName();

            if ($maxVersion > $currentVersion) {
                $storageAdapter->setBundleVersion($bundleName, $maxVersion);
                echo $currentVersion . ' => ' . $maxVersion . ' # ' . $bundleName . PHP_EOL;
                $changed = true;
            } else {
                echo $currentVersion . ' no changes # ' . $bundleName . PHP_EOL;
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

    public function setAction()
    {
        $request = $this->getRequest();

        $bundleName = $request->getParam('bundle');
        $version  = $request->getParam('version');

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
                echo $currentVersion . ' => ' . $version . ' # ' . $bundleName . PHP_EOL;
                $changed = true;
            } else {
                echo $currentVersion . ' no changes # ' . $bundleName . PHP_EOL;
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

    public function updateAction()
    {


        echo __METHOD__;
    }

    public function installAction()
    {


        echo __METHOD__;
    }

}

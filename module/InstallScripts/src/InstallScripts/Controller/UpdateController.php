<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface as Color;


class UpdateController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('update');

        $scriptName = $this->getRequest()->getParam('script');

        $storageAdapter = $this->getStorage()->getAdapter();

        $changed = false;

        $scripts = $this->getLocator()->getScripts();
        foreach ($scripts as $script) {

            if (!empty($scriptName)) {
                $currentScriptName = str_replace('\\', '', $script->getName());
                $searchScriptName  = str_replace('\\', '', $scriptName);

                if ($currentScriptName != $searchScriptName) {
                    continue;
                }
            }

            $currentVersion =
                    $storageAdapter->getScriptVersion($script->getName());

            $versions = $script->getVersionsSorted();

            foreach ($versions AS $installVersion => $method) {

                if (version_compare($currentVersion, $installVersion) >= 0) {
                    continue;
                }

                $result = $this->execute($script, $method);

                if ($result) {
                    $storageAdapter->setScriptVersion(
                        $script->getName(),
                        $installVersion
                    );

                    echo  $this->colorize($currentVersion, Color::NORMAL, 7);
                    echo  $this->colorize(' => ', Color::NORMAL);
                    echo  $this->colorize($installVersion, Color::NORMAL, 7);
                    echo  $this->colorize($script->getName(), Color::BLUE);
                    echo PHP_EOL;

                    $currentVersion = $installVersion;
                    $changed = true;
                } else {
                    echo  $this->colorize($installVersion, Color::NORMAL, 7);
                    echo  $this->colorize('install failed ', Color::RED);
                    echo  $this->colorize($script->getName(), Color::BLUE);
                    echo PHP_EOL;
                }
            }
        }

        if ($changed) {
            if ($storageAdapter->save()) {
                echo $this->colorize('Saved', Color::LIGHT_GREEN) .  PHP_EOL;
            } else {
                echo $this->colorize('Failed to save data', Color::RED) .  PHP_EOL;
            }
        } else {
            echo $this->colorize('Nothing to save', Color::GREEN) .  PHP_EOL;
        }
    }

}

<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use InstallScripts\Exception\ActionControllerException;
use Zend\Console\ColorInterface as Color;


class InstallController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('install');

        $request = $this->getRequest();

        $scriptName     = $request->getParam('script');
        $installVersion = $request->getParam('version');

        $storageAdapter = $this->getStorage()->getAdapter();

        $changed = false;


        $scripts = $this->getLocator()->getScripts();
        foreach ($scripts as $script) {
            $currentScriptName = str_replace('\\', '', $script->getName());
            $searchScriptName  = str_replace('\\', '', $scriptName);

            if ($currentScriptName != $searchScriptName) {
                continue;
            }

            $currentVersion =
                    $storageAdapter->getScriptVersion($script->getName());

            $versions = $script->getVersionsSorted();

            if (!array_key_exists($installVersion, $versions)) {
                throw new ActionControllerException(
                    'Script "' . $script->getName() . '" '
                    . 'have no version "' . $installVersion . '". '
                    . 'Available versions: '
                    . implode(' ; ', array_keys($versions))
                );
            }

            $method = $versions[$installVersion];

            $result = $this->execute($script, $method);

            if ($result) {
                $storageAdapter->setScriptVersion(
                    $script->getName(),
                    $installVersion
                );

                echo $this->colorize($currentVersion, Color::LIGHT_MAGENTA, 7);
                echo $this->colorize(' => ', Color::NORMAL);
                echo $this->colorize($installVersion, Color::LIGHT_CYAN, 7);
                echo $this->colorize($scriptName, Color::BLUE);
                echo PHP_EOL;

                $changed = true;
            } else {
                echo $this->colorize($currentVersion, Color::NORMAL, 7);
                echo $this->colorize('install failed ', Color::RED);
                echo $this->colorize($scriptName, Color::BLUE);
                echo PHP_EOL;
            }
        }

        if ($changed) {
            if ($storageAdapter->save()) {
                echo $this->colorize('Saved', Color::LIGHT_GREEN);
                echo PHP_EOL;
            } else {
                echo $this->colorize('Failed to save data', Color::RED);
                echo PHP_EOL;
            }
        } else {
            echo $this->colorize('Nothing to save', Color::GREEN);
            echo PHP_EOL;
        }
    }

}

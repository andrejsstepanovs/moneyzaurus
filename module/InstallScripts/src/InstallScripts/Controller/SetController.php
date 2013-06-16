<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface as Color;


class SetController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('set');

        $request = $this->getRequest();

        $scriptName = $request->getParam('script');
        $version    = $request->getParam('version');

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

            if ($version != $currentVersion) {

                $versions = $script->getVersions();
                if (!array_key_exists($version, $versions)) {
                    echo  $this->colorize(
                        'Version "' . $version . '" dose not exist. '
                        . 'Available versions: '
                        . implode(', ', array_keys($versions)),
                        Color::RED
                    );
                    echo PHP_EOL;
                    break;
                }

                $storageAdapter->setScriptVersion(
                    $script->getName(),
                    $version
                );

                echo  $this->colorize($currentVersion, Color::LIGHT_MAGENTA, 7);
                echo  $this->colorize(' => ', Color::NORMAL);
                echo  $this->colorize($version, Color::LIGHT_CYAN, 7);
                echo  $this->colorize($scriptName, Color::BLUE);
                echo PHP_EOL;

                $changed = true;
            } else {
                echo  $this->colorize($currentVersion, Color::NORMAL, 7);
                echo  $this->colorize('no changes  ', Color::MAGENTA);
                echo  $this->colorize($scriptName, Color::BLUE);
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

<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use InstallScripts\Exception\ActionControllerException;
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

                if (!method_exists($script, $method)) {
                    throw new ActionControllerException(
                        'Script "' . $script->getName() . '" '
                        . 'have no method "' . $method . '"'
                    );
                }

                if (!is_callable(array($script, $method))) {
                    throw new ActionControllerException(
                        'Script method "' . $script->getName()
                        . '::' . $method . '()" is not callable'
                    );
                }

                $script->setMvcEvent($this->getEvent());

                $result = call_user_method($versions[$installVersion], $script);

                if ($result) {
                    $storageAdapter->setScriptVersion(
                        $script->getName(),
                        $installVersion
                    );

                    echo str_pad($currentVersion, 7);
                    echo ' => ';
                    echo str_pad($installVersion, 7);
                    echo  $this->colorize($script->getName(), Color::BLUE);
                    echo PHP_EOL;

                    $currentVersion = $installVersion;
                    $changed = true;
                } else {
                    echo str_pad($currentVersion, 7);
                    echo  $this->colorize('install failed ', Color::MAGENTA);
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

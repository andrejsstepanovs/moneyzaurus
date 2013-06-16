<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface as Color;


class SetLatestController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('set-latest');

        $storageAdapter = $this->getStorage()->getAdapter();

        $changed = false;

        $scripts = $this->getLocator()->getScripts();
        foreach ($scripts as $script) {
            $scriptName     = $script->getName();

            $currentVersion = $storageAdapter->getScriptVersion($scriptName);
            $maxVersion     = $script->getMaxVersion();

            if (version_compare($currentVersion, $maxVersion) < 0) {
                $storageAdapter->setScriptVersion($scriptName, $maxVersion);

                echo  $this->colorize($currentVersion, Color::NORMAL, 7);
                echo  $this->colorize(' => ', Color::NORMAL);
                echo  $this->colorize($maxVersion, Color::NORMAL, 7);
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
                echo $this->colorize('Saved', Color::LIGHT_GREEN) .  PHP_EOL;
            } else {
                echo $this->colorize('Failed to save data', Color::RED) .  PHP_EOL;
            }
        } else {
            echo $this->colorize('Nothing to save', Color::GREEN) .  PHP_EOL;
        }
    }

}

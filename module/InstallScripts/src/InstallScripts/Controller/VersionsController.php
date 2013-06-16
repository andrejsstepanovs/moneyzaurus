<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface as Color;


class VersionsController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('versions');

        $scriptName = $this->getRequest()->getParam('script');

        $scripts = $this->getLocator()->getScripts();
        foreach ($scripts as $script) {

            if (!empty($scriptName)) {
                $currentScriptName = str_replace('\\', '', $script->getName());
                $searchScriptName  = str_replace('\\', '', $scriptName);

                if ($currentScriptName != $searchScriptName) {
                    continue;
                }
            }

            echo $this->getTitle($script->getName(), Color::CYAN);

            $currentVersion = $this->getStorage()
                                   ->getAdapter()
                                   ->getScriptVersion($script->getName());

            $versions = $script->getVersionsSorted();
            foreach ($versions as $version => $method) {

                $compare = version_compare($currentVersion, $version);

                if ($compare < 0) {
                    $versionLabel = 'not installed';
                    $color = Color::RED;

                } elseif ($compare == 0) {
                    $versionLabel = 'currently installed';
                    $color = Color::LIGHT_GREEN;

                } else {
                    $versionLabel = 'installed';
                    $color = Color::GREEN;
                }

                echo $this->colorize($version, Color::NORMAL, 7);
                echo $this->colorize($versionLabel, $color, 25);
                echo $this->colorize($method . '()', Color::NORMAL);
                echo PHP_EOL;
            }
        }
    }

}

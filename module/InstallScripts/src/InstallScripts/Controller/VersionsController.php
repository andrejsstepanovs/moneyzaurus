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
                    $versionLabel = $this->colorize(
                        'not installed', Color::RED);

                } elseif ($compare == 0) {
                    $versionLabel = $this->colorize(
                        'currently installed', Color::LIGHT_GREEN);

                } else {
                    $versionLabel = $this->colorize(
                        'installed', Color::GREEN);
                }

                echo str_pad($version, 7) . ' ';
                echo str_pad($versionLabel, 50) . ' ';
                echo $method . '()' . PHP_EOL;
            }
        }
    }

}

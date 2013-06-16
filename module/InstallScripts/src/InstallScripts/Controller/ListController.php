<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Console\ColorInterface as Color;


class ListController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('list');

        $storageAdapter = $this->getStorage()->getAdapter();
        $scripts = $this->getLocator()->getScripts();

        foreach ($scripts as $script) {
            $scriptName    = $script->getName();
            $scriptVersion = $storageAdapter->getScriptVersion($scriptName);

            echo $this->colorize($scriptVersion, Color::NORMAL, 7);
            echo $this->colorize($scriptName, Color::CYAN);
            echo PHP_EOL;
        }
    }

}

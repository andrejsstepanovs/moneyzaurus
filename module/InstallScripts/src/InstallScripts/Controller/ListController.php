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
            $scriptName = $script->getName();

            echo str_pad($storageAdapter->getScriptVersion($scriptName), 7);
            echo $this->colorize($scriptName, Color::CYAN) . PHP_EOL;
        }
    }

}

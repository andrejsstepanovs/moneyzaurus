<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Debug\Debug as ZendDebug;
use Zend\Console\ColorInterface as Color;


class ConfigController extends AbstractActionController
{

    public function indexAction()
    {
        echo $this->getTitle('config');


        $zendDebug = new ZendDebug();

        $configOutput  = $zendDebug->dump(
            $this->getConfig(),
            '### Config Data  ###',
            false
        );

        $storageOutput = $zendDebug->dump(
            $this->getStorage()->getAdapter()->load(),
            '### Storage Data ###',
            false
        );


        echo $this->colorize($configOutput,  Color::CYAN) . PHP_EOL;
        echo $this->colorize($storageOutput, Color::GRAY) . PHP_EOL;
    }

}

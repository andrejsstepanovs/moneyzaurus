<?php

namespace InstallScripts\Controller;

use InstallScripts\Controller\AbstractActionController;
use Zend\Debug\Debug as ZendDebug;
use Zend\Console\ColorInterface;


class ConfigController extends AbstractActionController
{
    /** @var \Zend\Debug\Debug */
    protected $dump;


    /**
     * @return \Zend\Debug\Debug
     */
    protected function getDump()
    {
        if (null === $this->dump) {
            $this->dump = new ZendDebug();
        }
        return $this->dump;
    }

    public function indexAction()
    {
        echo $this->getTitle('config');

        $storage = $this->getInstallScriptStorage();
        $config  = $this->getConfig();

        $dump = $this->getDump();

        $configOutput  = $dump->dump($config, 'Config Data', false);
        $storageOutput = $dump->dump($storage->getAdapter()->load(), 'Storage Data', false);

        echo $this->colorize($configOutput,  ColorInterface::CYAN) . PHP_EOL;
        echo $this->colorize($storageOutput, ColorInterface::GRAY) . PHP_EOL;
    }

}

<?php

namespace InstallScripts\Controller;

use InstallScripts\Model\AbstractActionController;
use InstallScripts\Model\Storage as InstallScriptStorage;

class IndexController extends AbstractActionController
{

    public function updateAction()
    {
        echo __METHOD__;
    }

    public function listAction()
    {
        $config = $this->getConfig();

        $storage = new InstallScriptStorage($config);

        $data = array(
            'aaa' => 'bbb'
        );

//        $a = $storage->setData($data)->save();
        $a = $storage->setData($data)->load();

        \DEBUG::dump($a);


        echo __METHOD__;
    }

    public function installAction()
    {
        echo __METHOD__;
    }

    public function configAction()
    {
        echo __METHOD__;
    }

    public function setAction()
    {
        $request = $this->getRequest();

        $resource = $request->getParam('bundle');
        $version  = $request->getParam('version');


        echo __METHOD__;
    }

    public function setLatestAction()
    {
        echo __METHOD__;
    }

}

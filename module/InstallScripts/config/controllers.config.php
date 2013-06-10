<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'InstallScripts\Controller\List'      => 'InstallScripts\Controller\ListController',
            'InstallScripts\Controller\Config'    => 'InstallScripts\Controller\ConfigController',
            'InstallScripts\Controller\Set'       => 'InstallScripts\Controller\SetController',
            'InstallScripts\Controller\SetLatest' => 'InstallScripts\Controller\SetLatestController',
            'InstallScripts\Controller\Install'   => 'InstallScripts\Controller\InstallController',
            'InstallScripts\Controller\Versions'  => 'InstallScripts\Controller\VersionsController',
            'InstallScripts\Controller\Update'    => 'InstallScripts\Controller\UpdateController',
        )
    )
);

<?php

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'install-scripts-update' => array(
                    'options' => array(
                        'route'    => 'install-scripts update',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Index',
                            'action'     => 'update'
                        )
                    )
                ),
                'install-scripts-list' => array(
                    'options' => array(
                        'route'    => 'install-scripts list',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Index',
                            'action'     => 'list'
                        )
                    )
                ),
                'install-scripts-install' => array(
                    'options' => array(
                        'route'    => 'install-scripts install <bundle> <version>',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Index',
                            'action'     => 'install'
                        ),
                        'constraints' => array(
                            'version' => '[\d.]'
                        ),
                    )
                ),
                'install-scripts-config' => array(
                    'options' => array(
                        'route'    => 'install-scripts config',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Index',
                            'action'     => 'config'
                        )
                    )
                ),
                'install-scripts-set' => array(
                    'options' => array(
                        'route'    => 'install-scripts set <bundle> <version>',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Index',
                            'action'     => 'set',
                        ),
                        'constraints' => array(
                            'version' => '[\d.]'
                        ),
                    )
                ),
                'install-scripts-set-latest' => array(
                    'options' => array(
                        'route'    => 'install-scripts set-latest',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Index',
                            'action'     => 'setLatest'
                        )
                    )
                ),
            )
        )
    )
);

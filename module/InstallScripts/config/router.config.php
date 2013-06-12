<?php

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'install-scripts-update' => array(
                    'options' => array(
                        'route'    => 'install-scripts update [<script>]',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Update',
                            'action'     => 'index'
                        )
                    )
                ),
                'install-scripts-list' => array(
                    'options' => array(
                        'route'    => 'install-scripts list',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\List',
                            'action'     => 'index'
                        )
                    )
                ),
                'install-scripts-versions' => array(
                    'options' => array(
                        'route'    => 'install-scripts versions [<script>]',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Versions',
                            'action'     => 'index'
                        )
                    )
                ),
                'install-scripts-install' => array(
                    'options' => array(
                        'route'    => 'install-scripts install <script> <version>',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Install',
                            'action'     => 'index'
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
                            'controller' => 'InstallScripts\Controller\Config',
                            'action'     => 'index'
                        )
                    )
                ),
                'install-scripts-set' => array(
                    'options' => array(
                        'route'    => 'install-scripts set <script> <version>',
                        'defaults' => array(
                            'controller' => 'InstallScripts\Controller\Set',
                            'action'     => 'index',
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
                            'controller' => 'InstallScripts\Controller\SetLatest',
                            'action'     => 'index'
                        )
                    )
                ),
            )
        )
    )
);

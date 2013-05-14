<?php

return array(
    'router' => array(
        'routes' => array(
            'moneyzaurus' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'new' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/new',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'pie' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/pie',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'chart' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/chart',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'list' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/list',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            )
        ),
    ),
);

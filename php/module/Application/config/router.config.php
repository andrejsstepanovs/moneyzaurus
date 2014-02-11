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
            'login' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/login',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Login',
                        'action'     => 'index',
                    ),
                ),
            ),
            'resend-password' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/resend-password[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\ResendPassword',
                        'action'     => 'index',
                    ),
                ),
            ),
            'logout' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/logout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Logout',
                        'action'     => 'index',
                    ),
                ),
            ),
            'register' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/register',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Register',
                        'action'     => 'index',
                    ),
                ),
            ),
            'transaction' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/transaction[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Transaction',
                        'action'     => 'index',
                    ),
                ),
            ),
            'user' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/user[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\User',
                        'action'     => 'index',
                    ),
                ),
            ),
            'connection' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/connection[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Connection',
                        'action'     => 'index',
                    ),
                ),
            ),
            'data' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/data[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Data',
                        'action'     => 'index',
                    ),
                ),
            ),
            'pie' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/pie[/:action]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Pie',
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
                    'route'    => '/list[/:action][/:id][/page/:page][/order_by/:order_by][/:order]',
                    'constraints' => array(
                        'action'   => '(?!\bpage\b)(?!\border_by\b)[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'       => '[0-9]+',
                        'page'     => '[0-9]+',
                        'order_by' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'order'    => 'ASC|DESC',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\List',
                        'action'     => 'index',
                    ),
                ),
            )
        ),
    ),
);

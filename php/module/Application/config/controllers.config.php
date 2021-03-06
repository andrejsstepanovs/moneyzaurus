<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index'          => 'Application\Controller\IndexController',
            'Application\Controller\Login'          => 'Application\Controller\LoginController',
            'Application\Controller\ResendPassword' => 'Application\Controller\ResendPasswordController',
            'Application\Controller\Logout'         => 'Application\Controller\LogoutController',
            'Application\Controller\Register'       => 'Application\Controller\RegisterController',
            'Application\Controller\Transaction'    => 'Application\Controller\TransactionController',
            'Application\Controller\User'           => 'Application\Controller\UserController',
            'Application\Controller\Connection'     => 'Application\Controller\ConnectionController',
            'Application\Controller\Data'           => 'Application\Controller\DataController',
            'Application\Controller\List'           => 'Application\Controller\ListController',
            'Application\Controller\Pie'            => 'Application\Controller\PieController',
            'Application\Controller\Chart'          => 'Application\Controller\ChartController',
        )
    )
);

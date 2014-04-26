<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
            'Application\Controller\Login',
            'Application\Controller\ResendPassword',
            'Application\Controller\Register',
        ),
        'user'  => array(
            'Application\Controller\User',
            'Application\Controller\Connection',
            'Application\Controller\Data',
            'Application\Controller\Logout',
            'Application\Controller\Transaction',
            'Application\Controller\List',
            'Application\Controller\Pie',
            'Application\Controller\Chart',
        ),
        'admin' => array(
            'admin'
        ),
    )
);

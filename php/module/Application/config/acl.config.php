<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
            'Application\Controller\Login',
            'Application\Controller\Register',
        ),
        'user'  => array(
            'Application\Controller\User',
            'Application\Controller\Logout',
            'Application\Controller\Transaction',
            'Application\Controller\List',
            'Application\Controller\Pie',
        ),
        'admin' => array(
            'admin'
        ),
    )
);
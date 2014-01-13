<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
            'Application\Controller\User',
        ),
        'user'  => array(
            'Application\Controller\Transaction',
            'Application\Controller\List',
            'Application\Controller\Pie',
        ),
        'admin' => array(
            'admin'
        ),
    )
);
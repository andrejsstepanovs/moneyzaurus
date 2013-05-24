<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
            'Application\Controller\User',
            'Application\Controller\Transaction',
        ),
        'user'  => array(
            'Application\Controller\New',
        ),
        'admin' => array(
            'admin'
        ),
    )
);
<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
            'Application\Controller\Login',
        ),
        'user'  => array(
            'Application\Controller\New',
        ),
        'admin' => array(
            'admin'
        ),
    )
);
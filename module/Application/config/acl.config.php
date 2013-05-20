<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
            'Application\Controller\Login',
            'zfcuser',
            'ScnSocialAuth-User',
        ),
        'user'  => array(
            'Application\Controller\New',
        ),
        'admin' => array(
            'admin'
        ),
    )
);
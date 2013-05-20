<?php
return array(
    'acl' => array(
        'guest' => array(
            'Application\Controller\Index',
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
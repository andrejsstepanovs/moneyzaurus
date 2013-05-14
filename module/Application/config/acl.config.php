<?php
return array(
    'acl' => array(
        'roles' => array(
            'guest' => \Application\Model\User::ROLE_NONE,
            'user'  => \Application\Model\User::ROLE_USER,
            'pro'   => \Application\Model\User::ROLE_PRO,
            'admin' => \Application\Model\User::ROLE_ADMIN,
        ),
        'resources' => array(
            'allow' => array(
                'user' => array(
                    'login' => 'guest',
                    'all'   => 'member'
                )
            )
        )
    )
);
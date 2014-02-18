<?php

return array(
    'session' => array(
        'config' => array(
            'use_cookies'         => true,
            'use_only_cookies'    => true,
            'cookie_httponly'     => true,
            'name'                => 'SESSID',
            'remember_me_seconds' => 7776000,
        ),
        'validator' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
);

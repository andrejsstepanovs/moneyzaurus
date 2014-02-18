<?php

return array(
    'session' => array(
        'config' => array(
            'use_cookies'      => true,
            'use_only_cookies' => true,
            'cookie_httponly'  => true,
            'name'             => 'SESSID',
        ),
        'validator' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
);

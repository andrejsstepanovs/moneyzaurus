<?php
$host = getenv('APP_MAIL_HOST');
$host = empty($host) ? 'smtp.gmail.com' : $host;

$username = getenv('APP_MAIL_USERNAME');
$username = empty($username) ? '*****@gmail.com' : $username;

$password = getenv('APP_MAIL_PASSWORD');
$password = empty($password) ? '*******' : $password;

$port = getenv('APP_MAIL_PORT');
$port = empty($port) ? '587' : $port;

$ssl = getenv('APP_MAIL_TLS');
$ssl = empty($ssl) ? 'tls' : $ssl;

return array(
    'mail' => array(
        'transport' => array(
            'options' => array(
                'host'              => $host,
                'connection_class'  => 'plain',
                'connection_config' => array(
                    'username'      => $username,
                    'port'          => $port,
                    'password'      => $password,
                    'ssl'           => $ssl
                )
            )
        )
    )
);

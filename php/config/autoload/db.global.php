<?php

$host = getenv('OPENSHIFT_MYSQL_DB_HOST');
$host = empty($host) ? 'localhost' : $host;

$port = getenv('OPENSHIFT_MYSQL_DB_PORT');
$port = empty($port) ? '3306' : $port;

$user = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
$user = empty($user) ? 'root' : $user;

$password = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
$password = empty($password) ? 'root' : $password;

$dbName = getenv('OPENSHIFT_APP_NAME');
$dbName = empty($dbName) ? 'app' : $dbName;

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=' . $dbName . ';host=' . $host . ';port=' . $port,
        'username'       => $user,
        'password'       => $password,
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'DbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
        )
    )
);

<?php

$dbParams = array(
    'database'  => 'moneyzaurus',
    'username'  => 'root',
    'password'  => 'root',
    'hostname'  => 'localhost',
    'port'      => '3306',
);

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
//        'username'  => $dbParams['username'],
//        'password'  => $dbParams['password'],
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
);

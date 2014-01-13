<?php

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql://$OPENSHIFT_MYSQL_DB_HOST:$OPENSHIFT_MYSQL_DB_PORT/',
        'username'       => 'adminGW1dnEU',
        'password'       => 'TiIF8Y6C88Dx',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
        )
    )
);

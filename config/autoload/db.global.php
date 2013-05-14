<?php

$dbParams = array(
    'database'  => 'moneyzaurus',
    'username'  => 'root',
    'password'  => 'root',
    'hostname'  => 'localhost',
    'port'      => '3306',
);

//return array(
//    'doctrine' => array(
//        'connection' => array(
//            'orm_default' => array(
//                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
//                'params' => array(
//                    'host'     => $dbParams['hostname'],
//                    'port'     => $dbParams['port'],
//                    'user'     => $dbParams['username'],
//                    'password' => $dbParams['password'],
//                    'dbname'   => $dbParams['database'],
//                )
//            )
//        )
//    ),
//);

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use ($dbParams) {
                return new Zend\Db\Adapter\Adapter(array(
                    'driver'    => 'pdo',
                    'dsn'       => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
                    'database'  => $dbParams['database'],
                    'username'  => $dbParams['username'],
                    'password'  => $dbParams['password'],
                    'hostname'  => $dbParams['hostname'],
                ));
            },
        ),
    ),
);

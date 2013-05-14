<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Log' => function ($sm) {
                $log = new Zend\Log\Logger();
                $writer = new Zend\Log\Writer\Stream('./data/logs/application.log');
                $log->addWriter($writer);

                return $log;
            },
        ),
    ),
);

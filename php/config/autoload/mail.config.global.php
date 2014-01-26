<?php

return array(
    'mail' => array(
        'transport' => array(
            'options' => array(
                'host'              => 'smtp.gmail.com',
                'connection_class'  => 'plain',
                'connection_config' => array(
                    'username'      => '******@gmail.com',
                    'port'          => '587',
                    'password'      => '******',
                    'ssl'           => 'tls'
                )
            )
        )
    )
);

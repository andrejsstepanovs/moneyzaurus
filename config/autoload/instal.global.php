<?php

return array(
    'InstallScripts' => array(
        'storage'   => array(
            'adapter' => 'InstallScripts\Storage\FileAdapter',
            'options' => array(
                'file' => __DIR__ . '/../../data/install.json',
            )
        ),
        'modules' => array(
            'Application' => array(
                'Install\Transactions',
            )
        ),
    )
);

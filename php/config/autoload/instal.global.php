<?php

return array(
    'InstallScripts' => array(
        'StorageAdapter' => array(
            'Adapter' => 'InstallScripts\StorageAdapter\FileStorageAdapter',
            'Options' => array(
                'file' => __DIR__ . '/../../data/install.json',
            )
        ),
        'Scripts' => array(
            'Application' => array(
                'Install\Transactions',
            )
        ),
    )
);

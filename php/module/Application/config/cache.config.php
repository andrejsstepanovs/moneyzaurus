<?php

return array(
    'data_cache' => array(
        'adapter' => 'filesystem',
        'options' => array(
            'cache_dir' => __DIR__ . '/../../../data/cache/data/',
            'ttl'       => 600,
            'namespace' => 'moneyzaurus',
        )
    )
);

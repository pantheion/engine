<?php

return [
    'app' => [
        'single-page-app' => false
    ],
    'auth' => [
        'driver' => 'session', // session || token
        'column' => 'email',
        'model' => 'App\\Model\\Member'
    ],
    'mercury' => [
        'hub' => "App\\Mercury\\Hub"
    ],
    'zephyr' => [
        'charon' => false
    ]
];

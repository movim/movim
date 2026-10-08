<?php

require 'vendor/autoload.php';

$bootstrap = new Movim\Bootstrap;
$bootstrap->boot(true);

return [
    'paths' => [
        'migrations' => DOCUMENT_ROOT . '/database/migrations',
        'seeds' => DOCUMENT_ROOT . '/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'movim',
        'movim' => [
            'adapter'   => config('database.driver'),
            'host'      => config('database.host'),
            'name'      => config('database.database'),
            'user'      => config('database.username'),
            'pass'      => config('database.password'),
            'port'      => config('database.port'),
        ]
    ]
];

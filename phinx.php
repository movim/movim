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
        'default_database' => 'movim',
        'default_environment' => 'movim',
        'movim' => [
            'adapter' => DB_TYPE,
            'host' => DB_HOST,
            'name' => DB_DATABASE,
            'user' => DB_USERNAME,
            'pass' => DB_PASSWORD,
            'port' => DB_PORT
        ]
    ]
];

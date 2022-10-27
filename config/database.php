<?php

/**
 * Movim database default values
 */

return [
    'driver'    => env('DB_DRIVER', 'pgsql'),
    'host'      => env('DB_HOST', 'localhost'),
    'port'      => (int)env('DB_PORT', 5432),
    'username'  => env('DB_USERNAME', 'movim'),
    'password'  => env('DB_PASSWORD', 'movim'),
    'database'  => env('DB_DATABASE', 'movim')
];
<?php

/**
 * Movim daemon default values
 */

return [
    'url'       => env('DAEMON_URL', null),
    'port'      => env('DAEMON_PORT', 8080),
    'interface' => env('DAEMON_INTERFACE', 'localhost'),
    'debug'     => env('DAEMON_DEBUG', false),
    'verbose'   => env('DAEMON_VERBOSE', false),
];

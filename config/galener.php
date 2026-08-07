<?php

/**
 * Movim Galener default values
 */

return [
    'xmpp_host'    => env('GALENER_XMPP_HOST', null),
    'xmpp_port'    => (int)env('GALENER_XMPP_PORT', 5347),
    'xmpp_password'=> env('GALENER_XMPP_PASSWORD', null),
    'galene_path'  => env('GALENER_GALENE_PATH', null),
];

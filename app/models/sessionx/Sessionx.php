<?php

namespace Modl;

class Sessionx extends Model
{
    public $session;
    public $username;
    public $hash;
    public $resource;
    public $host;
    public $config;
    public $active;
    public $start;
    public $timestamp;

    public $_struct = [
        'session'   => ['type' => 'string','size' => 32,'key' => true],
        'jid'       => ['type' => 'string','size' => 64],
        'username'  => ['type' => 'string','size' => 64],
        'hash'      => ['type' => 'string','size' => 64],
        'resource'  => ['type' => 'string','size' => 16],
        'host'      => ['type' => 'string','size' => 64,'mandatory' => true],
        'config'    => ['type' => 'text'],
        'active'    => ['type' => 'int','size' => 4],
        'start'     => ['type' => 'date'],
        'timestamp' => ['type' => 'date']
    ];
}

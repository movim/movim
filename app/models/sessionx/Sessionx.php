<?php

namespace modl;

class Sessionx extends Model {
    public $session;
    public $username;
    public $hash;
    public $resource;
    public $host;
    public $config;
    public $active;
    public $start;
    public $timestamp;

    public function __construct() {
        $this->_struct = '
        {
            "session" :
                {"type":"string", "size":32, "key":true },
            "username" :
                {"type":"string", "size":64 },
            "hash" :
                {"type":"string", "size":64 },
            "resource" :
                {"type":"string", "size":16 },
            "host" :
                {"type":"string", "size":64,  "mandatory":true },
            "config" :
                {"type":"text" },
            "active" :
                {"type":"int",    "size":4 },
            "start" :
                {"type":"date" },
            "timestamp" :
                {"type":"date" }
        }';

        parent::__construct();
    }
}

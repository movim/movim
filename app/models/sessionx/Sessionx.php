<?php

namespace modl;

class Sessionx extends Model {
    public $session;
    public $username;
    public $hash;
    public $resource;
    public $rid;
    public $sid;
    public $id;
    public $port;
    public $host;
    public $domain;
    public $config;
    public $active;
    public $start;
    public $timestamp;
    public $mechanism;

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
            "rid" :
                {"type":"int",    "size":8,   "mandatory":true },
            "sid" :
                {"type":"string", "size":64 },
            "id" :
                {"type":"int",    "size":8,   "mandatory":true },
            "port" :
                {"type":"int",    "size":5,   "mandatory":true },
            "host" :
                {"type":"string", "size":64,  "mandatory":true },
            "domain" :
                {"type":"string", "size":64,  "mandatory":true },
            "config" :
                {"type":"text" },
            "active" :
                {"type":"int",    "size":4 },
            "start" :
                {"type":"date" },
            "timestamp" :
                {"type":"date" },
            "mechanism" :
                {"type":"string", "size":16 }
        }';

        parent::__construct();
    }
}

<?php

namespace modl;

class Sessionx extends Model {
    public $session;
    public $username;
    public $password;
    public $ressource;
    public $rid;
    public $sid;
    public $id;
    public $url;
    public $port;
    public $host;
    public $domain;
    public $config;
    public $active;
    public $start;
    public $timestamp;
    
    public function __construct() {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "username" : 
                {"type":"string", "size":64 },
            "password" : 
                {"type":"string", "size":64 },
            "ressource" : 
                {"type":"string", "size":64 },
            "rid" : 
                {"type":"int",    "size":8,   "mandatory":true },
            "sid" : 
                {"type":"string",    "size":64 },
            "id" : 
                {"type":"int",    "size":8,   "mandatory":true },
            "url" : 
                {"type":"string", "size":128, "mandatory":true },
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
                {"type":"date" }
        }';
        
        parent::__construct();
    }
}

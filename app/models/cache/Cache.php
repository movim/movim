<?php

namespace modl;

class Cache extends Model{
    public $session;
    public $name;
    public $data;
    public $timestamp;

    public function __construct() {
        $this->_struct = '
        {
            "session" :
                {"type":"string", "size":64, "key":true },
            "name" :
                {"type":"string", "size":32, "key":true },
            "data" :
                {"type":"text", "mandatory":true },
            "timestamp" :
                {"type":"date", "mandatory":true }
        }';

        parent::__construct();
    }
}

<?php

namespace modl;

class Cache extends ModlModel{
    public $session;
    public $key;
    public $data;
    public $timestamp;
    
    public function __construct() {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "key" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "data" : 
                {"type":"text", "mandatory":true },
            "timestamp" : 
                {"type":"date" }
        }';
        
        parent::__construct();
    }
}

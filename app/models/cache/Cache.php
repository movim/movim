<?php

namespace modl;

class Cache extends ModlModel{
    public $session;
    public $data;
    public $checksum;
    public $timestamp;
    
    public function __construct() {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "data" : 
                {"type":"text", "mandatory":true },
            "checksum" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "timestamp" : 
                {"type":"date" }
        }';
        
        parent::__construct();
    }
}

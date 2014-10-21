<?php

namespace modl;

class Session extends Model {    
    public $name;
    public $value;
    public $session;
    public $container;
    public $timestamp;
    
    public function __construct() {
        $this->_struct = '
        {
            "name" : 
                {"type":"string", "size":32, "mandatory":true, "key":true },
            "value" : 
                {"type":"text", "mandatory":true },
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "container" : 
                {"type":"string", "size":16, "mandatory":true, "key":true },
            "timestamp" : 
                {"type":"date" }
        }';
        
        parent::__construct();
    }
}

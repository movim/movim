<?php

namespace modl;

class Subscription extends ModlModel {    
    public $jid;
    public $server;
    public $node;
    public $subscription;
    public $subid;
    public $title;
    public $timestamp;
    public $name;
    
    public function __construct() {
        $this->_struct = '
        {
            "jid" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "server" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "node" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "subscription" : 
                {"type":"string", "size":128, "mandatory":true },
            "subid" : 
                {"type":"string", "size":128 },
            "title" : 
                {"type":"string", "size":128 },
            "timestamp" : 
                {"type":"date" }
        }';
        
        parent::__construct();
    }

    

    function set($jid, $server, $node, $s) {
        $this->jid          = $jid;
        $this->server       = $server;
        $this->node         = $node;
        $this->jid          = (string)$s->attributes()->jid;
        $this->subscription = (string)$s->attributes()->subscription;
        $this->subid        = (string)$s->attributes()->subid;
        $this->timestamp    = date('Y-m-d H:i:s', rand(1111111111, 8888888888));
        
        if($this->subid = '')
            $this->subid = 'default';
    }
}

<?php

namespace modl;

class Subscription extends Model {    
    public $jid;
    public $server;
    public $node;
    public $subscription;
    public $subid;
    public $title;
    public $description;
    public $tags;
    public $timestamp;
    public $name;
    
    public function __construct() {
        $this->_struct = '
        {
            "jid" : 
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "server" : 
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "node" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "subscription" : 
                {"type":"string", "size":128, "mandatory":true },
            "subid" : 
                {"type":"string", "size":128 },
            "title" : 
                {"type":"string", "size":128 },
            "tags" : 
                {"type":"text" },
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
        $this->tags         = serialize(array());
        
        if($this->subid = '')
            $this->subid = 'default';
    }
}

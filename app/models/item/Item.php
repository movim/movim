<?php

namespace modl;

class Item extends ModlModel {
    public $server;
    public $jid;
    public $name;
    public $node;
    public $updated;
    public $subscription;
    public $num;
    
    public function __construct() {
        $this->_struct = '
        {
            "server" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "jid" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "node" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "name" : 
                {"type":"string", "size":128 },
            "updated" : 
                {"type":"date"}
        }';
        
        parent::__construct();
    }

    public function set($item, $from) {
        $this->server = $from;
        $this->node   = (string)$item->attributes()->node;
        $this->jid    = (string)$item->attributes()->jid;
        if($this->jid == null)
            $this->jid = $this->node;
        $this->name   = (string)$item->attributes()->name;
        $this->updated  = date('Y-m-d H:i:s');
    }
    
    public function getName() {
        if($this->name != null)
            return $this->name;
        elseif($this->node != null)
            return $this->node;
        else
            return $this->jid;
    }
}

class Server extends ModlModel {
    public $server;
    public $number;
}

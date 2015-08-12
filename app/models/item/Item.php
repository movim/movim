<?php

namespace modl;

class Item extends Model {
    public $server;
    public $jid;
    public $name;
    public $node;
    public $creator;
    public $created;
    public $updated;
    public $description;
    public $subscription;
    public $num;
    public $sub;
    
    public function __construct() {
        $this->_struct = '
        {
            "server" : 
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "jid" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "node" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "creator" : 
                {"type":"string", "size":128 },
            "name" : 
                {"type":"string", "size":128 },
            "created" : 
                {"type":"date"},
            "description" : 
                {"type":"text" },
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

    public function setMetadata($metadata, $from, $node) {
        $this->server = $from;
        $this->jid = $from;
        $this->node = $node;

        foreach($metadata->children() as $i) {
            $key = (string)$i->attributes()->var;

            switch ($key) {
                case 'pubsub#title':
                    $this->name = (string)$i->value;
                    break;
                case 'pubsub#creator':
                    $this->creator = (string)$i->value;
                    break;
                case 'pubsub#creation_date':
                    $this->created = (string)$i->value;
                    break;
                case 'pubsub#description':
                    $this->description = (string)$i->value;
                    break;
            }
        }
        
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

class Server extends Model {
    public $server;
    public $number;
}

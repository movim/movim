<?php

namespace modl;

class Node extends ModlModel {
    public $serverid;
    public $nodeid;
    public $title;
    public $config;
    public $updated;
    public $subscription;
    
    public function __construct() {
        $this->_struct = "
        {
            'serverid' : 
                {'type':'string', 'size':128, 'mandatory':true },
            'nodeid' : 
                {'type':'string', 'size':128, 'mandatory':true },
            'title' : 
                {'type':'string', 'size':128 },
            'config' : 
                {'type':'text' },
            'updated' : 
                {'type':'date'}
        }";
    }

    public function set($item, $serverid) {
        $this->serverid = $serverid;
        $this->nodeid = (string)$item->attributes()->node;
        $this->title = (string)$item->attributes()->name;
        $this->updated = date('Y-m-d H:i:s');
    }
    
    public function getName() {
        if($this->title == '')
            return $this->nodeid;
        else
            return $this->title;
    }
}

class Server extends ModlModel {
    public $serverid;
    public $number;
}

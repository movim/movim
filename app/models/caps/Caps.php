<?php

namespace modl;

class Caps extends Model {
    public $node;
    public $category;
    public $type;
    public $name;
    public $features;
    
    public function __construct() {
        $this->_struct = '
        {
            "node" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "category" : 
                {"type":"string", "size":128, "mandatory":true },
            "type" : 
                {"type":"string", "size":128, "mandatory":true },
            "name" : 
                {"type":"string", "size":128, "mandatory":true },
            "features" : 
                {"type":"text", "mandatory":true }
        }';
        
        parent::__construct();
    }
    
    public function set($query, $node = false) {
        if(!$node)
            $this->node     = (string)$query->query->attributes()->node;
        else
            $this->node     = $node;
            
        if(
            isset($query->query) && 
            isset($query->query->identity) && 
            $query->query->identity->attributes()) {
            $this->category = (string)$query->query->identity->attributes()->category;
            $this->type     = (string)$query->query->identity->attributes()->type;
            $this->name     = (string)$query->query->identity->attributes()->name;
        }
        
        $fet = array();
        foreach($query->query->feature as $f) {
            array_push($fet, (string)$f->attributes()->var);
        }
        $this->features = serialize($fet);
        
    }
}

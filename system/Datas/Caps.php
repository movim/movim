<?php

class Caps extends DatajarBase {
    protected $node;
    protected $category;
    protected $type;
    protected $name;
    protected $features;
    
    protected function type_init() {
        $this->node      = DatajarType::varchar(256);
        $this->category  = DatajarType::varchar(128);
        $this->type      = DatajarType::varchar(128);
        $this->name      = DatajarType::varchar(128);
        $this->features  = DatajarType::text();
    }
    
    public function setCaps($query) {
        $this->node->setval($query['@attributes']['node']);
        $this->category->setval($query['identity']['@attributes']['category']);
        $this->type->setval($query['identity']['@attributes']['type']);
        $this->name->setval($query['identity']['@attributes']['name']);
        $this->features->setval(serialize($query['feature']));
    }
    
    public function getData($data) {
        return $this->$data->getval();
    }
}

class CapsHandler {
    private $instance;

    public function __construct() {
    	$this->instance = new Caps();
    }
    
    public function get($node) {
	    global $sdb;
        $sdb->load($this->instance, array('node' => $node));
        return $this->instance;
    }
}

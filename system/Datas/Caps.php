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
        
        \movim_log($query);
        $this->node->setval((string)$query->query->attributes()->node);
        $this->category->setval((string)$query->query->identity->attributes()->category);
        $this->type->setval((string)$query->query->identity->attributes()->type);
        $this->name->setval((string)$query->query->identity->attributes()->name);
        
        $fet = array();
        foreach($query->query->feature as $f) {
            array_push($fet, (string)$f->attributes()->var);
        }
        $this->features->setval(serialize($fet));
        
    }
    
    public function getData($data) {
        return $this->$data->getval();
    }
}

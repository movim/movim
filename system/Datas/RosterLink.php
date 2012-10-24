<?php

class RosterLink extends DatajarBase {
    public $key;
    public $jid;
    
    public $rostername;
    public $rosterask;
    public $rostersubscription;
    
    public $group;
    
    public $chaton;
    
    protected function type_init() {
        $this->key      = DatajarType::varchar(128);
        $this->jid      = DatajarType::varchar(128);
        
        $this->rostername     = DatajarType::varchar(128);
        $this->rosterask      = DatajarType::varchar(128);
        $this->rostersubscription = DatajarType::varchar(128);
        
        $this->group    = DatajarType::varchar(128);
        
        $this->chaton  = DatajarType::int();
    }
    
    public function getData($data) {
        return trim($this->$data->getval());
    }
}

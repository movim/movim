<?php

class RosterLink extends DatajarBase {
    public $key;
    public $jid;
    
    public $rostername;
    public $rosterask;
    public $rostersubscription;
    
    public $realname;
    
    public $group;
    
    public $chaton;
    
    protected function type_init() {
        $this->key      = DatajarType::varchar(128);
        $this->jid      = DatajarType::varchar(128);
        
        $this->rostername     = DatajarType::varchar(128);
        $this->rosterask      = DatajarType::varchar(128);
        $this->rostersubscription = DatajarType::varchar(128);
        
        $this->realname = DatajarType::varchar(128);
        
        $this->group    = DatajarType::varchar(128);
        
        $this->chaton  = DatajarType::int();
    }
    
    public function getData($data) {
        return trim($this->$data->getval());
    }
    
    public function getTrueName() {
        $truename = '';
        if(isset($this->realname) && $this->realname->getval() != '' && !filter_var($this->realname->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->realname->getval();
        elseif(isset($this->rostername) && $this->rostername->getval() != '' && !filter_var($this->rostername->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->rostername->getval();
        else
            $truename = $this->jid->getval();

        return $truename;
    }
}

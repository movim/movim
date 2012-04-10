<?php

class Contact extends DatajarBase {
    protected $key;
    protected $jid;
    
    protected $fn;
    protected $name;
    protected $date;
    protected $url;
    
    protected $gender;
    protected $marital;
    
    protected $group;
    
    protected $rostername;
    protected $rosterask;
    protected $rostersubscription;
    
    protected $phototype;
    protected $photobin;
    
    protected $desc;
    
    protected $vcardreceived;
    protected $chaton;
    protected $public;
    
    protected function type_init() {
        $this->key      = DatajarType::varchar(128);
        $this->jid      = DatajarType::varchar(128);
        
        $this->fn       = DatajarType::varchar(128);
        $this->name     = DatajarType::varchar(128);
        $this->date     = DatajarType::date();
        $this->url      = DatajarType::varchar(128);
        
        $this->gender   = DatajarType::varchar(1);
        $this->marital  = DatajarType::varchar(20);
        
        $this->group    = DatajarType::varchar(128);
        
        $this->rostername     = DatajarType::varchar(128);
        $this->rosterask      = DatajarType::varchar(128);
        $this->rostersubscription = DatajarType::varchar(128);
        
        $this->phototype = DatajarType::varchar(128);
        $this->photobin  = DatajarType::text();
        
        $this->desc = DatajarType::text();
        
        $this->vcardreceived  = DatajarType::int();
        $this->chaton  = DatajarType::int();
        $this->public  = DatajarType::int();
    }
    
    public function setContact($array) {
        $user = new User();
        
        $date = strtotime($array['vCard']['BDAY']);
        if($date != false) 
            $this->date->setval(date('Y-m-d', $date)); 
                   
        $this->key->setval($user->getLogin());
        $this->jid->setval(($array['@attributes']['from'] != NULL) ? $array['@attributes']['from'] : $user->getLogin());
        
        $this->name->setval($array['vCard']['NICKNAME']);
        $this->fn->setval($array['vCard']['FN']);
        $this->url->setval($array['vCard']['URL']);
        
        $this->gender->setval($array['vCard']['X-GENDER']);
        $this->marital->setval($array['vCard']['MARITAL']['STATUS']);
        
        if($this->rostersubscription->getval() == false)
            $this->rostersubscription->setval('none');
        
        $this->phototype->setval($array['vCard']['PHOTO']['TYPE']);
        $this->photobin->setval($array['vCard']['PHOTO']['BINVAL']);
        
        $this->desc->setval($array['vCard']['DESC']);
        
        $this->vcardreceived->setval(1);
        $this->public->setval(0);
    }

    public function getTrueName() {
        $truename = '';
        if(isset($this->fn) && $this->fn->getval() != '' && !filter_var($this->fn->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->fn->getval();
        elseif(isset($this->name) && $this->name->getval() != '' && !filter_var($this->name->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->name->getval();
        elseif(isset($this->rostername) && $this->rostername->getval() != '' && !filter_var($this->rostername->getval(), FILTER_VALIDATE_EMAIL)) 
            $truename = $this->rostername->getval();
        else
            $truename = $this->jid->getval();

        return $truename;
    }
    
    public function getData($data) {
        return $this->$data->getval();
    }
    
    public function getPhoto($size = 'normal') {
        if(
               isset($this->phototype) 
            && isset($this->photobin) 
            && $this->phototype->getval() != '' 
            && $this->photobin->getval() != ''
            && $this->phototype->getval() != 'f' 
            && $this->photobin->getval() != 'f'
        ) {
            $str = 'image.php?c='.$this->jid->getval().'&size='.$size;
        } else {
            $str = 'image.php?c=default';
        }
        return $str;
    }
    
}

class ContactHandler {
    private $instance;

    public function __construct() {
    	$this->instance = new Contact();
    }
    
    public function get($jid) {
	    global $sdb;
    	$user = new User();
        $sdb->load($this->instance, array('key' => $user->getLogin(), 'jid' => $jid));
        return $this->instance;
    }
}

<?php

class Contact extends StorageBase {
    protected $key;
    protected $jid;
    
    protected $fn;
    protected $name;
    protected $date;
    protected $url;
    
    protected $rostername;
    protected $rosterask;
    protected $rostersubscription;
    
    protected $phototype;
    protected $photobin;
    
    protected $desc;
    
    protected $vcardreceived;
    protected $chaton;
    
    protected function type_init() {
        $this->key      = StorageType::varchar(128);
        $this->jid      = StorageType::varchar(128);
        
        $this->fn       = StorageType::varchar(128);
        $this->name     = StorageType::varchar(128);
        $this->date     = StorageType::date();
        $this->url      = StorageType::varchar(128);
        
        $this->rostername     = StorageType::varchar(128);
        $this->rosterask      = StorageType::varchar(128);
        $this->rostersubscription = StorageType::varchar(128);
        
        $this->phototype = StorageType::varchar(128);
        $this->photobin  = StorageType::text();
        
        $this->desc = StorageType::text();
        
        $this->vcardreceived  = StorageType::int();
        $this->chaton  = StorageType::int();
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
        
        $this->phototype->setval($array['vCard']['PHOTO']['TYPE']);
        $this->photobin->setval($array['vCard']['PHOTO']['BINVAL']);
        
        $this->desc->setval($array['vCard']['DESC']);
        
        $this->vcardreceived->setval(1);
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
        if(isset($this->phototype) && isset($this->photobin) && $this->phototype->getval() != '' && $this->photobin->getval() != '') {
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

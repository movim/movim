<?php

class Contact extends DatajarBase {
    public $key;
    public $jid;
    
    public $fn;
    public $name;
    public $date;
    public $url;
    
    public $gender;
    public $marital;
    
    public $group;
    
    public $rostername;
    public $rosterask;
    public $rostersubscription;
    
    public $phototype;
    public $photobin;
    
    public $desc;
    
    public $vcardreceived;
    public $chaton;
    public $public;
    
    // User Mood (contain serialized array) - XEP 0107
    public $mood;
    
    // User Activity (contain serialized array) - XEP 0108
    public $activity;
    
    // User Nickname - XEP 0172
    public $nickname;
    
    // User Tune - XEP 0118
    public $tuneartist;
    public $tunelenght;
    public $tunerating;
    public $tunesource;
    public $tunetitle;
    public $tunetrack;
    public $tuneuri;
    
    // User Location 
    public $loclatitude;
    public $loclongitude;
    public $localtitude;
    public $loccountry;
    public $loccountrycode;
    public $locregion;
    public $locpostalcode;
    public $loclocality;
    public $locstreet;
    public $locbuilding;
    public $loctext;
    public $locuri;
    public $loctimestamp;
    
    
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
        
        $this->mood            = DatajarType::varchar(128);
        $this->activity        = DatajarType::varchar(128);
        $this->nickname        = DatajarType::varchar(128);
        
        $this->tuneartist      = DatajarType::varchar(128);
        $this->tunelenght      = DatajarType::int();
        $this->tunerating      = DatajarType::int();
        $this->tunesource      = DatajarType::varchar(128);
        $this->tunetitle       = DatajarType::varchar(128);
        $this->tunetrack       = DatajarType::varchar(128);
        $this->tuneuri;
        
        $this->loclatitude     = DatajarType::varchar(128);
        $this->loclongitude    = DatajarType::varchar(128);
        $this->localtitude     = DatajarType::int();
        $this->loccountry      = DatajarType::varchar(128);
        $this->loccountrycode  = DatajarType::varchar(128);
        $this->locregion       = DatajarType::varchar(128);
        $this->locpostalcode   = DatajarType::varchar(128);
        $this->loclocality     = DatajarType::varchar(128);
        $this->locstreet       = DatajarType::varchar(128);
        $this->locbuilding     = DatajarType::varchar(128);
        $this->loctext         = DatajarType::varchar(128);
        $this->locuri          = DatajarType::varchar(128);
        $this->loctimestamp    = DatajarType::datetime();
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
    
    public function setContactRosterItem($item) {
        $user = new User();

        $this->key->setval($user->getLogin());
        $this->jid->setval($item['@attributes']['jid']);
        $this->rostername->setval($item['@attributes']['name']);
        $this->rosterask->setval($item['@attributes']['ask']);
        $this->rostersubscription->setval($item['@attributes']['subscription']);
        $this->group->setval($item['group']);
    }

    public function getTrueName() {
        $truename = '';
        if(isset($this->fn) && $this->fn->getval() != '' && !filter_var($this->fn->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->fn->getval();
        elseif(isset($this->nickname) && $this->nickname->getval() != '' && !filter_var($this->nickname->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->nickname->getval();
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
    
    public function getPlace() {
        $place = '';
        
        if($this->locbuilding->getval() != '')
            $place .= $this->locbuilding->getval().' ';
        if($this->locstreet->getval() != '')
            $place .= $this->locstreet->getval().'<br />';
        if($this->locpostalcode->getval() != '')
            $place .= $this->locpostalcode->getval().' ';
        if($this->loclocality->getval() != '')
            $place .= $this->loclocality->getval().'<br />';
        if($this->locregion->getval() != '')
            $place .= $this->locregion->getval().' - ';
        if($this->loccountry->getval() != '')
            $place .= $this->loccountry->getval();
        return $place;
    }
    
    
    public function setContactTune($stanza) {

    }
    
    public function setContactGeoloc($stanza) {
        $this->loclatitude->setval((string)$stanza->item->geoloc->lat);
        $this->loclongitude->setval((string)$stanza->item->geoloc->lon);
        $this->localtitude->setval((string)$stanza->item->geoloc->alt);
        $this->loccountry->setval((string)$stanza->item->geoloc->country);
        $this->loccountrycode->setval((string)$stanza->item->geoloc->countrycode);
        $this->locregion->setval((string)$stanza->item->geoloc->region);
        $this->locpostalcode->setval((string)$stanza->item->geoloc->postalcode);
        $this->loclocality->setval((string)$stanza->item->geoloc->locality);
        $this->locstreet->setval((string)$stanza->item->geoloc->street);
        $this->locbuilding->setval((string)$stanza->item->geoloc->building);
        $this->loctext->setval((string)$stanza->item->geoloc->text);
        $this->locuri->setval((string)$stanza->item->geoloc->uri);
        $this->loctimestamp->setval(date(
                            'Y-m-d H:i:s', 
                            strtotime((string)$stanza->item->geoloc->timestamp)));
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

<?php

class Contact extends DatajarBase {
    //public $key;
    public $jid;
    
    public $fn;
    public $name;
    public $date;
    public $url;
    
    public $email;
    
    public $adrlocality;
    public $adrpostalcode;
    public $adrcountry;
    
    public $gender;
    public $marital;
    
    public $phototype;
    public $photobin;
    
    public $desc;
    
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
        $this->jid      = DatajarType::varchar(128);
        
        $this->fn       = DatajarType::varchar(128);
        $this->name     = DatajarType::varchar(128);
        $this->date     = DatajarType::date();
        $this->url      = DatajarType::varchar(128);

        $this->email    = DatajarType::varchar(128);
        
        $this->adrlocality   = DatajarType::varchar(128);
        $this->adrpostalcode = DatajarType::int();
        $this->adrcountry    = DatajarType::varchar(128);
        
        $this->gender   = DatajarType::varchar(1);
        $this->marital  = DatajarType::varchar(20);
        
        $this->phototype = DatajarType::varchar(128);
        $this->photobin  = DatajarType::text();
        
        $this->desc = DatajarType::text();

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

    public function getTrueName() {
        $truename = '';
        if(isset($this->fn) && $this->fn->getval() != '' && !filter_var($this->fn->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->fn->getval();
        elseif(isset($this->nickname) && $this->nickname->getval() != '' && !filter_var($this->nickname->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->nickname->getval();
        elseif(isset($this->name) && $this->name->getval() != '' && !filter_var($this->name->getval(), FILTER_VALIDATE_EMAIL))
            $truename = $this->name->getval();
        else
            $truename = $this->jid->getval();

        return $truename;
    }
    
    public function getData($data) {
        return trim($this->$data->getval());
    }
    
    public function getAge() {
        if(isset($this->date) && $this->date->getval() != '0000-00-00') {
            return  floor( (strtotime(date('Y-m-d')) - strtotime($this->date->getval())) / 31556926).' '.t('yo');
        } else {
            return '';
        }
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
    
    public function getPhoto($size = 'normal', $jid = false) {
        if($jid != false)
            $str = 'image.php?c='.$jid.'&size='.$size;
        elseif(
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
    
    static public function getPhotoFromJid($size, $jid) {
        $c = new Contact();
        return $c->getPhoto($size, $jid);
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
        $sdb->load($this->instance, array('jid' => $jid));
        return $this->instance;
    }
}

<?php

class Presence extends DatajarBase {
    public $key;
    public $jid;
    
    // General presence informations
    public $ressource;
    public $presence;
    public $priority;
    public $status;
    
    // Client Informations
    public $node;
    public $ver;
    
    // Delay - XEP 0203
    public $delay;
    
    // User Mood (contain serialized array) - XEP 0107
    public $mood;
    
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
        $this->key             = DatajarType::varchar(128);
        $this->jid             = DatajarType::varchar(128);
        
        $this->ressource       = DatajarType::varchar(128);
        $this->presence        = DatajarType::int();
        $this->priority        = DatajarType::int();
        $this->status          = DatajarType::text();
        
        $this->node            = DatajarType::varchar(128);
        $this->ver             = DatajarType::varchar(128);

        $this->delay           = DatajarType::datetime();
        
        $this->mood            = DatajarType::varchar(128);
        
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
        $this->loctimestamp       = DatajarType::datetime();
    }
    
    public function setPresence($stanza) {
        $to = current(explode('/',(string)$stanza->attributes()->to));
        $jid = explode('/',(string)$stanza->attributes()->from);

        $this->key->setval($to);
        $this->jid->setval($jid[0]);
        $this->ressource->setval($jid[1]);
        $this->status->setval((string)$stanza->status);
        
        if($stanza->c) {
            $this->node->setval((string)$stanza->c->attributes()->node);
            $this->ver->setval((string)$stanza->c->attributes()->ver);
        }
        
        if($stanza->priority)
            $this->priority->setval((string)$stanza->priority);
        
        if((string)$stanza->attributes()->type == 'error') {
            $this->presence->setval(6);    
        } elseif((string)$stanza->attributes()->type == 'unavailable') {
            $this->presence->setval(5);
        } elseif((string)$stanza->show == 'away') {
            $this->presence->setval(2);
        } elseif((string)$stanza->show == 'dnd') {
            $this->presence->setval(3);
        } elseif((string)$stanza->show == 'xa') {
            $this->presence->setval(4);
        } else {
            $this->presence->setval(1);
        }
        
        if($stanza->delay) {
            $this->delay->setval(
                        date(
                            'Y-m-d H:i:s', 
                            strtotime(
                                (string)$stanza->delay->attributes()->stamp
                                )
                            )
                        );
        }
    }
    
    public function setPresenceTune($stanza) {
        movim_log($stanza);
    }
    
    public function setPresenceGeoloc($stanza) {
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
    
    public function getPresence() {
        $txt = array(
                1 => 'online',
                2 => 'away',
                3 => 'dnd',
                4 => 'xa',
                5 => 'offline',
                6 => 'server_error'
            );
    
        $arr = array();
        $arr['jid'] = $this->jid->getval();
        $arr['ressource'] = $this->ressource->getval();
        $arr['presence'] = $this->presence->getval();
        $arr['presence_txt'] = $txt[$this->presence->getval()];
        $arr['priority'] = $this->priority->getval();
        $arr['status'] = $this->status->getval();
        $arr['node'] = $this->node->getval();
        $arr['ver'] = $this->ver->getval();
        
        return $arr;
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
}

class PresenceHandler {
    private $instance;
    
    public function __contruct() {
        $this->instance = new Presence();
    }
    
    static public function getPresence($jid, $one = false, $ressource = false) {
	    global $sdb;
    	$user = new User();
    	if($ressource == false)
	        $presences = $sdb->select('Presence', array('key' => $user->getLogin(), 'jid' => $jid)); 
	    else
	        $presences = $sdb->select('Presence', array('key' => $user->getLogin(), 'jid' => $jid, 'ressource' => $ressource)); 
	    
	    if($presences != false) {
	        $arr = array();
	        
	        $n = 5;
	        $x = 0;
	        $i = 0;
	        
	        foreach($presences as $presence) {
	            $tmp = $presence->getPresence();
	            if($tmp['presence'] <= $n) {
	                $x = $i;
	                $n = $tmp['presence'];
	            }
	            array_push($arr, $tmp);
	            
	            $i++;
	        }
	        
	        if($one == true)
	            return $arr[$x];
	        else
	            return $arr;
	     }
    }
    
    static public function clearPresence() {
    	$user = new User();
        
        $query = Presence::query()
                            ->where(
                                array('key' => $user->getLogin()));
        $presences = Presence::run_query($query);

        foreach($presences as $presence)
            $presence->delete();
    }
}

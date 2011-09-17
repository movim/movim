<?php

class Presence extends StorageBase {
    protected $key;
    protected $jid;
    
    protected $ressource;
    protected $presence;
    protected $priority;
    protected $status;
    
    protected function type_init() {
        $this->key      = StorageType::varchar(128);
        $this->jid      = StorageType::varchar(128);
        
        $this->ressource = StorageType::varchar(128);
        $this->presence = StorageType::int();
        $this->priority = StorageType::int();
        $this->status   = StorageType::text();
    }
    
    public function setPresence($array) {
        $xmpp = Jabber::getInstance();        
        list($jid, $ressource) = explode('/',$array['@attributes']['from']);
        
        $this->key->setval($xmpp->getCleanJid());
        $this->jid->setval($jid);
        $this->ressource->setval($ressource);
        $this->status->setval($array['status']);
        
        $this->priority->setval($array['priority']);
        
        if($array['@attributes']['type'] == 'error') {
            $this->presence->setval(6);    
        } elseif($array['@attributes']['type'] == 'unavailable') {
            $this->presence->setval(5);
        } elseif($array['show'] == 'away') {
            $this->presence->setval(2);
        } elseif($array['show'] == 'dnd') {
            $this->presence->setval(3);
        } elseif($array['show'] == 'xa') {
            $this->presence->setval(4);
        } else {
            $this->presence->setval(1);
        }
    }
    
    public function getPresence() {
        $txt = array(
                1 => 'online',
                2 => 'away',
                3 => 'dnd',
                4 => 'xa',
                5 => 'offline'
            );
    
        $arr = array();
        $arr['jid'] = $this->jid->getval();
        $arr['ressource'] = $this->ressource->getval();
        $arr['presence'] = $this->presence->getval();
        $arr['presence_txt'] = $txt[$this->presence->getval()];
        $arr['priority'] = $this->priority->getval();
        $arr['status'] = $this->status->getval();
        
        return $arr;
    }
}

class PresenceHandler {
    public function __contruct() {

    }
    
    public function getPresence($jid, $one = false) {
	    global $sdb;
    	$user = new User();
	    $presences = $sdb->select('Presence', array('key' => $user->getLogin(), 'jid' => $jid)); 
	    
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
    
    public function clearPresence() {
        global $sdb;
    	$user = new User();
        $presences = $sdb->select('Presence', array('key' => $user->getLogin())); 
 
        foreach($presences as $presence)
            $sdb->delete($presence);
    }
}

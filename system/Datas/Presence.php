<?php

class Presence extends DatajarBase {
    public $key;
    public $jid;
    
    public $ressource;
    public $presence;
    public $priority;
    public $status;
    
    public $node;
    public $ver;
    
    protected function type_init() {
        $this->key      = DatajarType::varchar(128);
        $this->jid      = DatajarType::varchar(128);
        
        $this->ressource = DatajarType::varchar(128);
        $this->presence = DatajarType::int();
        $this->priority = DatajarType::int();
        $this->status   = DatajarType::text();
        
        $this->node     = DatajarType::varchar(128);
        $this->ver      = DatajarType::varchar(128);
    }
    
    public function setPresence($array) {
        $xmpp = Jabber::getInstance();        
        list($jid, $ressource) = explode('/',$array['@attributes']['from']);
        
        $this->key->setval($xmpp->getCleanJid());
        $this->jid->setval($jid);
        $this->ressource->setval($ressource);
        $this->status->setval($array['status']);
        
        $this->node->setval($array['c']['@attributes']['node']);
        $this->ver->setval($array['c']['@attributes']['ver']);
        
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

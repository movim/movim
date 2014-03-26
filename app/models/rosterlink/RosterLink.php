<?php

namespace modl;

class RosterLink extends Model {    
    public $session;
    public $jid;
    
    public $rostername;
    public $rosterask;
    public $rostersubscription;
    
    public $realname;
    
    public $groupname;
    
    public $chaton;

    public $publickey;
    
    public function __construct() {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "jid" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "rostername" : 
                {"type":"string", "size":128 },
            "rosterask" : 
                {"type":"string", "size":128 },
            "rostersubscription" : 
                {"type":"string", "size":128 },
            "realname" : 
                {"type":"string", "size":128 },
            "groupname" : 
                {"type":"string", "size":128 },
            "chaton" : 
                {"type":"int", "size":11 }
        }';
        
        parent::__construct();
    }

    
    function set($stanza) {
        $this->jid = (string)$stanza->attributes()->jid;
            
        if(isset($stanza->attributes()->name) && (string)$stanza->attributes()->name != '')
            $this->rostername = (string)$stanza->attributes()->name;
        else
            $this->rostername = (string)$stanza->attributes()->jid;
        $this->rosterask = (string)$stanza->attributes()->ask;
        $this->rostersubscription = (string)$stanza->attributes()->subscription;
        $this->groupname = (string)$stanza->group;
    }
}

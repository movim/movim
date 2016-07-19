<?php

namespace modl;

class RosterLink extends Model {
    public $session;
    public $jid;
    public $rosterask;
    public $rostersubscription;
    public $publickey;
    protected $rostername;
    protected $groupname;

    public function __construct() {
        $this->_struct = '
        {
            "session" :
                {"type":"string", "size":64, "key":true },
            "jid" :
                {"type":"string", "size":96, "key":true },
            "rostername" :
                {"type":"string", "size":96 },
            "rosterask" :
                {"type":"string", "size":16 },
            "rostersubscription" :
                {"type":"string", "size":4, "mandatory":true },
            "groupname" :
                {"type":"string", "size":64 }
        }';

        parent::__construct();
    }


    function set($stanza) {
        $this->jid = (string)$stanza->attributes()->jid;

        if(isset($stanza->attributes()->name)
        && (string)$stanza->attributes()->name != '')
            $this->__set('rostername', (string)$stanza->attributes()->name);
        else
            $this->__set('rostername',     (string)$stanza->attributes()->jid);
        $this->__set('rosterask',          (string)$stanza->attributes()->ask);
        $this->__set('rostersubscription', (string)$stanza->attributes()->subscription);
        $this->__set('groupname',          (string)$stanza->group);
    }
}

<?php

namespace modl;

class RosterLink extends Model
{
    public $session;
    public $jid;
    public $rosterask;
    public $rostersubscription;
    public $publickey;
    public $rostername;
    public $groupname;

    public $_struct = [
        'session'       => ['type' => 'string','size' => 64,'key' => true],
        'jid'           => ['type' => 'string','size' => 96,'key' => true],
        'rostername'    => ['type' => 'string','size' => 96],
        'rosterask'     => ['type' => 'string','size' => 16],
        'rostersubscription' => ['type' => 'string','size' => 4,'mandatory' => true],
        'groupname'     => ['type' => 'string','size' => 64]
    ];

    function set($stanza)
    {
        $this->jid = (string)$stanza->attributes()->jid;

        if(isset($stanza->attributes()->name)
        && (string)$stanza->attributes()->name != '')
            $this->rostername = (string)$stanza->attributes()->name;
        else
            $this->rostername = (string)$stanza->attributes()->jid;
        $this->rosterask = (string)$stanza->attributes()->ask;
        $this->rostersubscription = (string)$stanza->attributes()->subscription;
        $this->groupname = (string)$stanza->group;
    }
}

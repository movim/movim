<?php

namespace modl;

class Subscription extends Model {
    public $jid;
    protected $server;
    protected $node;
    protected $subscription;
    protected $subid;
    protected $title;
    public $description;
    public $tags;
    public $timestamp;
    public $name;
    public $servicename;

    public function __construct() {
        $this->_struct = '
        {
            "jid" :
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "server" :
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "node" :
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "subscription" :
                {"type":"string", "size":128, "mandatory":true },
            "subid" :
                {"type":"string", "size":128 },
            "title" :
                {"type":"string", "size":128 },
            "tags" :
                {"type":"text" },
            "timestamp" :
                {"type":"date" }
        }';

        parent::__construct();
    }

    function set($jid, $server, $node, $s) {
        $this->__set('jid',             $jid);
        $this->__set('server',          $server);
        $this->__set('node',            $node);
        $this->__set('jid',             (string)$s->attributes()->jid);
        $this->__set('subscription',    (string)$s->attributes()->subscription);
        $this->__set('subid',           (string)$s->attributes()->subid);
        $this->__set('tags', serialize(array()));

        if($this->subid = '')
            $this->subid = 'default';
    }
}

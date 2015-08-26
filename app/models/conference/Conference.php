<?php

namespace modl;

class Conference extends Model {
    public $jid;
    protected $conference;
    protected $name;
    protected $nick;
    public $autojoin;
    public $status;

    public function __construct() {
        $this->_struct = '
        {
            "jid" :
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "conference" :
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "name" :
                {"type":"string", "size":128, "mandatory":true },
            "nick" :
                {"type":"string", "size":128 },
            "autojoin" :
                {"type":"int", "size":1 },
            "status" :
                {"type":"int", "size":1 }
        }';

        parent::__construct();
    }
}

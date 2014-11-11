<?php

namespace modl;

class Message extends Model {
    public $session;
    public $jidto;
    public $jidfrom;
    
    public $ressource;
    
    public $type;

    public $subject;
    public $thread;
    public $body;
    public $html;

    public $published;
    public $delivered;

    public function __construct() {
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":128, "mandatory":true },
            "jidto" : 
                {"type":"string", "size":128, "mandatory":true },
            "jidfrom" : 
                {"type":"string", "size":128, "mandatory":true },
            "ressource" : 
                {"type":"string", "size":128 },
            "type" : 
                {"type":"string", "size":20 },
            "subject" : 
                {"type":"text"},
            "thread" : 
                {"type":"string", "size":128 },
            "body" : 
                {"type":"text"},
            "html" : 
                {"type":"text"},
            "published" : 
                {"type":"date"},
            "delivered" : 
                {"type":"date"}
        }';
        
        parent::__construct();
    }
}

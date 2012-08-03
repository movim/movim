<?php

class Message extends DatajarBase {
    public $key;
    public $to;
    public $from;
    
    public $type;

    public $subject;
    public $thread;
    public $body;

    public $published;
    public $delivered;

    protected function type_init() {
        $this->key      = DatajarType::varchar(128);
        $this->to       = DatajarType::varchar(128);
        $this->from     = DatajarType::varchar(128); 
        
        $this->type     = DatajarType::varchar(20);
        
        $this->subject  = DatajarType::text();
        $this->thread   = DatajarType::varchar(128);
        $this->body     = DatajarType::text();

        $this->published = DatajarType::datetime();
        $this->delivered = DatajarType::datetime();
    }

    public function getData($data) {
        return $this->$data->getval();
    }

    public function setMessageChat($item) {
        $user = new User();

        $this->key->setval($user->getLogin());
        $this->to->setval(reset(explode("/",$item['@attributes']['to'])));
        $this->from->setval(reset(explode("/",$item['@attributes']['from'])));
        
        $this->type->setval("chat");
        
        $this->body->setval($item['body']);
        
        $this->published->setval(date('Y-m-d H:i:s'));
        $this->delivered->setval(date('Y-m-d H:i:s'));
    }
}

class MessageHandler {
    private $instance;

    public function __construct() {
    	$this->instance = new Message();
    }
    
    public function get($jid) {
	    global $sdb;
    	$user = new User();
        $sdb->load($this->instance, array('key' => $user->getLogin(), 'jid' => $jid));
        return $this->instance;
    }
}

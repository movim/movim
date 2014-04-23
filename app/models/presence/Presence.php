<?php

namespace Modl;

class Presence extends Model {
    protected $id;
    
    protected $session;
    protected $jid;
    
    // General presence informations
    protected $ressource;
    protected $value;
    protected $priority;
    protected $status;
    
    // Client Informations
    protected $node;
    protected $ver;
    
    // Delay - XEP 0203
    protected $delay;
    
    // Last Activity - XEP 0256
    protected $last;

    // Current Jabber OpenPGP Usage - XEP-0027
    protected $publickey;
    
    public function __construct() {
        $this->_struct = '
        {
            "id" : 
                {"type":"string", "size":128, "mandatory":true },
            "session" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "jid" : 
                {"type":"string", "size":128, "mandatory":true, "key":true },
            "ressource" : 
                {"type":"string", "size":128, "key":true },
            "value" : 
                {"type":"int",    "size":11, "mandatory":true },
            "priority" : 
                {"type":"int",    "size":11 },
            "status" : 
                {"type":"text"},
            "node" : 
                {"type":"string", "size":128 },
            "ver" : 
                {"type":"string", "size":128 },
            "delay" : 
                {"type":"date"},
            "last" : 
                {"type":"int",    "size":11 },
            "publickey" : 
                {"type":"text"}
        }';
        
        parent::__construct();
    }
    
    public function setPresence($stanza) {
        $jid = explode('/',(string)$stanza->attributes()->from);
        
        if($stanza->attributes()->to)
            $to = current(explode('/',(string)$stanza->attributes()->to));
        else
            $to = $jid[0];

        $this->session = $to;
        $this->jid = $jid[0];
        if(isset($jid[1]))
            $this->ressource = $jid[1];
        else
            $this->ressource = 'default';
            
        $this->status = (string)$stanza->status;
        
        if($stanza->c) {
            $this->node = (string)$stanza->c->attributes()->node;
            $this->ver = (string)$stanza->c->attributes()->ver;
        }
        
        if($stanza->priority)
            $this->priority = (string)$stanza->priority;
        
        if((string)$stanza->attributes()->type == 'error') {
            $this->value = 6;    
        } elseif((string)$stanza->attributes()->type == 'unavailable') {
            $this->value = 5;
        } elseif((string)$stanza->show == 'away') {
            $this->value = 2;
        } elseif((string)$stanza->show == 'dnd') {
            $this->value = 3;
        } elseif((string)$stanza->show == 'xa') {
            $this->value = 4;
        } else {
            $this->value = 1;
        }

        // Specific XEP
        if($stanza->x) {
            foreach($stanza->children() as $name => $c) {
                $ns = $c->getNamespaces(true);
                switch($ns['']) {
                    case 'jabber:x:signed' :
                        $this->publickey = (string)$c;
                        break;
                }
            }
        }
        
        if($stanza->delay) {
            $this->delay = 
                        date(
                            'Y-m-d H:i:s', 
                            strtotime(
                                (string)$stanza->delay->attributes()->stamp
                                )
                            )
                        ;
        }
        
        if($stanza->query) {
            $this->last = (int)$stanza->query->attributes()->seconds;
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
        $arr['jid'] = $this->jid;
        $arr['ressource'] = $this->ressource;
        $arr['presence'] = $this->value;
        $arr['presence_txt'] = $txt[$this->value];
        $arr['priority'] = $this->priority;
        $arr['status'] = $this->status;
        $arr['node'] = $this->node;
        $arr['ver'] = $this->ver;
        
        return $arr;
    }

    public function isChatroom() {
        if(filter_var($this->jid, FILTER_VALIDATE_EMAIL))
            return false;
        else
            return true;
    }
}

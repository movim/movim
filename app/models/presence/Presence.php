<?php

namespace Modl;

class Presence extends Model {
    protected $id;

    protected $session;
    protected $jid;

    // General presence informations
    protected $resource;
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
    protected $muc;
    protected $mucjid;
    protected $mucaffiliation;
    protected $mucrole;

    // vcard-temp:x:update, not saved in the DB
    public $photo = false;

    private $created;
    private $updated;

    public function __construct() {
        $this->_struct = '
        {
            "id" :
                {"type":"string", "size":64, "mandatory":true },
            "session" :
                {"type":"string", "size":64, "key":true },
            "jid" :
                {"type":"string", "size":64, "key":true },
            "resource" :
                {"type":"string", "size":64, "key":true },
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
                {"type":"text"},
            "muc" :
                {"type":"int",    "size":1 },
            "mucjid" :
                {"type":"string", "size":64 },
            "mucaffiliation" :
                {"type":"string", "size":32 },
            "mucrole" :
                {"type":"string", "size":32 },
            "created" :
                {"type":"date"},
            "updated" :
                {"type":"date"}
        }';

        parent::__construct();
    }

    public function setPresence($stanza) {
        $jid = explode('/',(string)$stanza->attributes()->from);

        if($stanza->attributes()->to)
            $to = current(explode('/',(string)$stanza->attributes()->to));
        else
            $to = $jid[0];

        $this->__set('session', $to);
        $this->__set('jid', $jid[0]);
        if(isset($jid[1]))
            $this->__set('resource', $jid[1]);
        else
            $this->__set('resource', 'default');

        $this->__set('status', (string)$stanza->status);

        if($stanza->c) {
            $this->__set('node', (string)$stanza->c->attributes()->node);
            $this->__set('ver', (string)$stanza->c->attributes()->ver);
        }

        if($stanza->priority)
            $this->__set('priority', (string)$stanza->priority);

        if((string)$stanza->attributes()->type == 'error') {
            $this->__set('value', 6);
        } elseif((string)$stanza->attributes()->type == 'unavailable') {
            $this->__set('value', 5);
        } elseif((string)$stanza->show == 'away') {
            $this->__set('value', 2);
        } elseif((string)$stanza->show == 'dnd') {
            $this->__set('value', 3);
        } elseif((string)$stanza->show == 'xa') {
            $this->__set('value', 4);
        } else {
            $this->__set('value', 1);
        }

        // Specific XEP
        if($stanza->x) {
            foreach($stanza->children() as $name => $c) {
                switch($c->attributes()->xmlns) {
                    case 'jabber:x:signed' :
                        $this->__set('publickey', (string)$c);
                        break;
                    case 'http://jabber.org/protocol/muc#user' :
                        $this->__set('muc            ', true);
                        if($c->item->attributes()->jid)
                            $this->__set('mucjid', cleanJid((string)$c->item->attributes()->jid));
                        else
                            $this->__set('mucjid', (string)$stanza->attributes()->from);

                        $this->__set('mucrole', (string)$c->item->attributes()->role);
                        $this->__set('mucaffiliation', (string)$c->item->attributes()->affiliation);
                        break;
                    case 'vcard-temp:x:update' :
                        $this->__set('photo', true);
                        break;
                }
            }
        }

        if($stanza->delay) {
            $this->__set('delay',
                        gmdate(
                            'Y-m-d H:i:s',
                            strtotime(
                                (string)$stanza->delay->attributes()->stamp
                                )
                            )
                        );
        }

        if($stanza->query) {
            $this->__set('last', (int)$stanza->query->attributes()->seconds);
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

        $arr = [];
        $arr['jid'] = $this->jid;
        $arr['resource'] = $this->resource;
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

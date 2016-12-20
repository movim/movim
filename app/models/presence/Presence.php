<?php

namespace Modl;

class Presence extends Model {
    public $id;

    public $session;
    public $jid;

    // General presence informations
    public $resource;
    public $value;
    public $priority;
    public $status;

    // Client Informations
    public $node;
    public $ver;

    // Delay - XEP 0203
    public $delay;

    // Last Activity - XEP 0256
    public $last;

    // Current Jabber OpenPGP Usage - XEP-0027
    public $publickey;
    public $muc;
    public $mucjid;
    public $mucaffiliation;
    public $mucrole;

    // vcard-temp:x:update, not saved in the DB
    public $photo = false;

    public $created;
    public $updated;

    public $_struct = [
        'id'        => ['type' => 'string','size' => 64,'mandatory' => true],
        'session'   => ['type' => 'string','size' => 64,'key' => true],
        'jid'       => ['type' => 'string','size' => 64,'key' => true],
        'resource'  => ['type' => 'string','size' => 64,'key' => true],
        'value'     => ['type' => 'int','size' => 11,'mandatory' => true],
        'priority'  => ['type' => 'int','size' => 11],
        'status'    => ['type' => 'text'],
        'node'      => ['type' => 'string','size' => 128],
        'ver'       => ['type' => 'string','size' => 128],
        'delay'     => ['type' => 'date'],
        'last'      => ['type' => 'int','size' => 11],
        'publickey' => ['type' => 'text'],
        'muc'       => ['type' => 'int','size' => 1],
        'mucjid'    => ['type' => 'string','size' => 64],
        'mucaffiliation' => ['type' => 'string','size' => 32],
        'mucrole'   => ['type' => 'string','size' => 32],
        'created'   => ['type' => 'date'],'updated' => ['type' => 'date'],
    ];

    public function setPresence($stanza)
    {
        $jid = explode('/',(string)$stanza->attributes()->from);

        if($stanza->attributes()->to)
            $to = current(explode('/',(string)$stanza->attributes()->to));
        else
            $to = $jid[0];

        $this->session = $to;
        $this->jid = $jid[0];
        if(isset($jid[1]))
            $this->resource = $jid[1];
        else
            $this->resource = 'default';

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
                switch($c->attributes()->xmlns) {
                    case 'jabber:x:signed' :
                        $this->publickey = (string)$c;
                        break;
                    case 'http://jabber.org/protocol/muc#user' :
                        $this->muc = true;
                        if($c->item->attributes()->jid)
                            $this->mucjid = cleanJid((string)$c->item->attributes()->jid);
                        else
                            $this->mucjid = (string)$stanza->attributes()->from;

                        $this->mucrole = (string)$c->item->attributes()->role;
                        $this->mucaffiliation = (string)$c->item->attributes()->affiliation;
                        break;
                    case 'vcard-temp:x:update' :
                        $this->photo = true;
                        break;
                }
            }
        }

        if($stanza->delay) {
            $this->delay = gmdate(
                'Y-m-d H:i:s',
                strtotime(
                    (string)$stanza->delay->attributes()->stamp
                )
            );
        }

        if($stanza->query) {
            $this->last = (int)$stanza->query->attributes()->seconds;
        }
    }

    public function getPresence()
    {
        $txt = [
                1 => 'online',
                2 => 'away',
                3 => 'dnd',
                4 => 'xa',
                5 => 'offline',
                6 => 'server_error'
            ];

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

    public function isChatroom()
    {
        if(filter_var($this->jid, FILTER_VALIDATE_EMAIL))
            return false;
        else
            return true;
    }
}

<?php

namespace App;

use Movim\Model;
use Movim\Image;
use Movim\Session;

class Presence extends Model
{
    protected $primaryKey = ['session_id', 'jid', 'resource'];
    public $incrementing = false;

    protected $attributes = [
        'session_id'    => SESSION_ID,
        'muc'    => false
    ];

    protected $fillable = [
        'session_id',
        'jid',
        'resource',
        'mucjid'
    ];

    public function roster()
    {
        return $this->hasOne('App\Roster', 'jid', 'jid')
                    ->where('session_id', $this->session_id);
    }

    public function capability()
    {
        return $this->hasOne('App\Info', 'node', 'node')
                    ->whereNull('server');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }

    public function getSeenAttribute()
    {
        if ($this->value == 1) return;

        if ($this->resource == '' && $this->delay) {
            $delay = strtotime($this->delay);
            if ($this->last) $delay += $this->last;

            return gmdate('Y-m-d H:i:s', $delay);
        } elseif ($this->delay) {
            return $this->delay;
        } elseif ($this->idle) {
            return $this->idle;
        }
    }

    public function getPresencetextAttribute()
    {
        return getPresences()[$this->value];
    }

    public function getPresencekeyAttribute()
    {
        return getPresencesTxt()[$this->value];
    }

    public function getConferencePictureAttribute()
    {
        return Image::getOrCreate($this->mucjid, 120);
    }

    public function getConferenceColorAttribute()
    {
        return stringToColor(
            $this->resource . 'groupchat'
        );
    }


    public static function findByStanza($stanza)
    {
        $temporary = new self;
        $temporary->set($stanza);
        return $temporary;
    }

    public function set($stanza)
    {
        $this->session_id = SESSION_ID;
        $jid = explodeJid($stanza->attributes()->from);
        $this->jid = $jid['jid'];
        $this->resource = $jid['resource'] ?? '';

        if ($stanza->status && !empty((string)$stanza->status)) {
            $this->status = (string)$stanza->status;
        }

        if ($stanza->c) {
            $this->node = (string)$stanza->c->attributes()->node .
                     '#'. (string)$stanza->c->attributes()->ver;
        }

        $this->priority = ($stanza->priority) ? (int)$stanza->priority : 0;

        if ((string)$stanza->attributes()->type == 'error') {
            $this->value = 6;
        } elseif ((string)$stanza->attributes()->type == 'unavailable'
               || (string)$stanza->attributes()->type == 'unsubscribed') {
            $this->value = 5;
        } elseif ((string)$stanza->show == 'away') {
            $this->value = 2;
        } elseif ((string)$stanza->show == 'dnd') {
            $this->value = 3;
        } elseif ((string)$stanza->show == 'xa') {
            $this->value = 4;
        } else {
            $this->value = 1;
        }

        // Specific XEP
        if ($stanza->x) {
            foreach ($stanza->children() as $name => $c) {
                switch ($c->attributes()->xmlns) {
                    /*case 'jabber:x:signed' :
                        $this->publickey = (string)$c;
                        break;*/
                    case 'http://jabber.org/protocol/muc#user':
                        if (!isset($c->item)) {
                            break;
                        }

                        $session = Session::start();

                        $this->muc = true;

                        /**
                         * If we were trying to connect to that particular MUC
                         * See Moxl\Xec\Action\Presence\Muc
                         */
                        if ($session->get((string)$stanza->attributes()->from)) {
                            $this->mucjid = \App\User::me()->id;
                        } elseif ($c->item->attributes()->jid) {
                            $this->mucjid = cleanJid((string)$c->item->attributes()->jid);
                        } else {
                            $this->mucjid = (string)$stanza->attributes()->from;
                        }

                        if ($c->item->attributes()->role) {
                            $this->mucrole = (string)$c->item->attributes()->role;
                        }
                        if ($c->item->attributes()->affiliation) {
                            $this->mucaffiliation = (string)$c->item->attributes()->affiliation;
                        }
                        break;
                    case 'vcard-temp:x:update':
                        if (!empty((string)$c->photo)) {
                            $this->avatarhash = (string)$c->photo;
                        }
                        break;
                }
            }
        }

        if ($stanza->delay && $stanza->delay->attributes()->xmlns == 'urn:xmpp:delay') {
            $this->delay = gmdate(
                'Y-m-d H:i:s',
                strtotime(
                    (string)$stanza->delay->attributes()->stamp
                )
            );
        }

        if ($stanza->idle && $stanza->idle->attributes()->xmlns == 'urn:xmpp:idle:1') {
            $this->idle = gmdate(
                'Y-m-d H:i:s',
                strtotime(
                    (string)$stanza->idle->attributes()->since
                )
            );
        }

        if ($stanza->query && $stanza->query->attributes()->xmlns == 'jabber:iq:last') {
            $this->last = (int)$stanza->query->attributes()->seconds;
        }
    }

    public function toArray()
    {
        $now = \Carbon\Carbon::now();
        return [
            'session_id' => $this->attributes['session_id'] ?? null,
            'jid' => $this->attributes['jid']  ?? null,
            'resource' => $this->attributes['resource'] ?? null,
            'value' => $this->attributes['value'] ?? null,
            'priority' => $this->attributes['priority'] ?? null,
            'status' => $this->attributes['status'] ?? null,
            'node' => $this->attributes['node'] ?? null,
            'delay' => $this->attributes['delay'] ?? null,
            'last' => $this->attributes['last'] ?? null,
            'idle' => $this->attributes['idle'] ?? null,
            'muc' => $this->attributes['muc'] ?? null,
            'mucjid' => $this->attributes['mucjid'] ?? null,
            'mucaffiliation' => $this->attributes['mucaffiliation']  ?? null,
            'mucrole' => $this->attributes['mucrole'] ?? null,
            'created_at' => $this->attributes['created_at'] ?? $now,
            'updated_at' => $this->attributes['updated_at'] ?? $now,
            'avatarhash' => $this->attributes['avatarhash'] ?? null,
        ];
    }
}

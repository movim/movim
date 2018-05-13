<?php

namespace App;

use CoenJacobs\EloquentCompositePrimaryKeys\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasCompositePrimaryKey;

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
        return $this->hasOne('App\Capability', 'node', 'node');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }

    public function contactConference()
    {
        return $this->hasOne('App\Contact', 'id', 'mucjid');
    }

    public function getPresencetextAttribute()
    {
        return getPresences()[$this->value];
    }

    public function getPresencekeyAttribute()
    {
        return getPresencesTxt()[$this->value];
    }

    public function getRefreshableAttribute()
    {
        if (!$this->avatarhash) return false;

        $count = \App\Contact::where('id', ($this->muc)
                                ? ($this->mucjid)
                                    ? $this->mucjid
                                    : $this->jid.'/'.$this->resource
                                : $this->jid)
                             ->where('avatarhash', (string)$this->avatarhash)
                             ->count();
        return ($count == 0)
            ? ($this->muc) ? $this->jid.'/'.$this->resource : $this->jid
            : false;
    }

    public static function findByStanza($stanza)
    {
        $jid = explode('/',(string)$stanza->attributes()->from);
        return self::firstOrNew([
            'session_id' => SESSION_ID,
            'jid' => $jid[0],
            'resource' => isset($jid[1]) ? $jid[1] : ''
        ]);
    }

    public function set($stanza)
    {
        $this->session_id = SESSION_ID;
        $jid = explode('/',(string)$stanza->attributes()->from);
        $this->jid = $jid[0];

        if (isset($jid[1])) {
            $this->resource = $jid[1];
        } else {
            $this->resource = '';
        }

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
        } elseif ((string)$stanza->attributes()->type == 'unavailable') {
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
                    case 'http://jabber.org/protocol/muc#user' :
                        if (!isset($c->item)) break;

                        $this->muc = true;
                        if ($c->item->attributes()->jid
                        && $c->item->attributes()->jid) {
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
                    case 'vcard-temp:x:update' :
                        $this->avatarhash = (string)$c->photo;
                        break;
                }
            }
        }

        if ($stanza->delay) {
            $this->delay = gmdate(
                'Y-m-d H:i:s',
                strtotime(
                    (string)$stanza->delay->attributes()->stamp
                )
            );
        }

        if ($this->muc && $this->avatarhash) {
            $resolved = \App\Contact::where('avatarhash', (string)$this->avatarhash)->first();
            if ($resolved) {
                $this->mucjid = $resolved->id;
            }
        }

        if ($stanza->query) {
            $this->last = (int)$stanza->query->attributes()->seconds;
        }
    }
}

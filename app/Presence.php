<?php

namespace App;

use Movim\Image;
use Movim\ImageSize;
use Movim\Session;
use Moxl\Xec\Action\Presence\Muc;
use Awobaz\Compoships\Database\Eloquent\Model;

class Presence extends Model
{
    protected $primaryKey = ['session_id', 'jid', 'mucjid', 'resource'];
    public $incrementing = false;

    protected $attributes = [
        'session_id' => SESSION_ID,
        'mucjid' => '', // Required to use it in the primary key
        'muc' => false
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

    public function getSeenAttribute(): ?string
    {
        if ($this->value == 1) return null;

        // XEP-0319
        if ($this->idle) {
            return $this->idle;
        }
        // ...supersedes XEP-0256
        elseif ($this->resource == '' && $this->delay) {
            $delay = strtotime($this->delay);
            if ($this->last) $delay += $this->last;

            return gmdate('Y-m-d H:i:s', $delay);
        } elseif ($this->delay) {
            return $this->delay;
        }

        return null;
    }

    /**
     * Fallback case if we don't have a contact
     */
    public function getPicture(ImageSize $size = ImageSize::M): string
    {
        return getPicture($this->jid, $this->jid, $size);
    }

    public function getPresencetextAttribute()
    {
        return getPresences()[$this->value];
    }

    public function getPresencekeyAttribute()
    {
        return getPresencesTxt()[$this->value];
    }

    public function getConferencePictureAttribute(): string
    {
        return Image::getOrCreate($this->mucjid, 120) ?? avatarPlaceholder($this->resource);
    }

    public function getConferenceColorAttribute(): string
    {
        return stringToColor($this->resource);
    }

    public static function findByStanza($stanza): Presence
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
        $this->type = $stanza->attributes()->type ?? null;

        if ($stanza->status && !empty((string)$stanza->status)) {
            $this->status = (string)$stanza->status;
        }

        if ($stanza->c) {
            $this->node = (string)$stanza->c->attributes()->xmlns == 'urn:xmpp:caps'
                ? 'urn:xmpp:caps#' . (string)$stanza->c->hash->attributes()->algo . '.' . (string)$stanza->c->hash
                : (string)$stanza->c->attributes()->node . '#' . (string)$stanza->c->attributes()->ver;
        }

        $this->priority = ($stanza->priority) ? (int)$stanza->priority : 0;

        if ((string)$stanza->attributes()->type == 'error') {
            $this->value = 6;
        } elseif (
            (string)$stanza->attributes()->type == 'unavailable'
            || (string)$stanza->attributes()->type == 'unsubscribed'
        ) {
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
                    case 'http://jabber.org/protocol/muc':
                    case 'http://jabber.org/protocol/muc#user':
                        $this->muc = true;

                        $session = Session::instance();

                        if ($session->get(Muc::$mucId . (string)$stanza->attributes()->from)) {
                            $this->mucjid = me()->id;
                        }

                        if (!isset($c->item)) {
                            break;
                        }

                        if (!empty($c->xpath("//status[@code='110']"))) {
                            $this->mucjid = me()->id;
                        } elseif ($c->item->attributes()->jid) {
                            $jid = explodeJid((string)$c->item->attributes()->jid);
                            $this->mucjid = $jid['jid'];
                            $this->mucjidresource = $jid['resource'];
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
            'resource' => $this->attributes['resource'] ?? '',
            'value' => $this->attributes['value'] ?? null,
            'type' => $this->attributes['type'] ?? null,
            'priority' => $this->attributes['priority'] ?? null,
            'status' => $this->attributes['status'] ?? null,
            'node' => $this->attributes['node'] ?? null,
            'delay' => $this->attributes['delay'] ?? null,
            'last' => $this->attributes['last'] ?? null,
            'idle' => $this->attributes['idle'] ?? null,
            'muc' => $this->attributes['muc'] ?? null,
            'mucjid' => $this->attributes['mucjid'] ?? '',
            'mucjidresource' => $this->attributes['mucjidresource'] ?? null,
            'mucaffiliation' => $this->attributes['mucaffiliation']  ?? null,
            'mucrole' => $this->attributes['mucrole'] ?? null,
            'created_at' => $this->attributes['created_at'] ?? $now,
            'updated_at' => $this->attributes['updated_at'] ?? $now,
            'avatarhash' => $this->attributes['avatarhash'] ?? null,
        ];
    }
}

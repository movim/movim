<?php

namespace App;

use Movim\Model;

class Roster extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'jid'];
    protected $fillable = ['jid', 'name', 'ask', 'subscription', 'group'];
    public $with = ['contact'];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public function save(array $options = [])
    {
        try {
            parent::save($options);
        } catch (\Exception $e) {
            \Utils::error($e->getMessage());
        }
    }

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence', 'jid', 'jid')
                    ->where('resource', '!=', '')
                    ->where('session_id', $this->session_id);
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'jid')
                    ->where('session_id', $this->session_id)
                    ->orderBy('value');
    }

    public function set($stanza)
    {
        $this->jid = (string)$stanza->attributes()->jid;

        $this->name = (isset($stanza->attributes()->name)
            && !empty((string)$stanza->attributes()->name))
            ? (string)$stanza->attributes()->name
            : null;

        $this->ask = $stanza->attributes()->ask
            ? (string)$stanza->attributes()->ask
            : null;

        $this->subscription = $stanza->attributes()->subscription
            ? (string)$stanza->attributes()->subscription
            : null;

        $this->group = $stanza->group
            ? (string)$stanza->group
            : null;
    }

    public static function saveMany(array $rosters)
    {
        $now = \Carbon\Carbon::now();
        $rosters = collect($rosters)->map(function (array $data) use ($now) {
            return array_merge([
                'created_at' => $now,
                'updated_at' => $now,
            ], $data);
        })->all();

        return Roster::insert($rosters);
    }

    public function getSearchTerms()
    {
        return cleanupId($this->jid).'-'.
            cleanupId($this->group);
    }

    public function getPhoto($size = 'm')
    {
        return getPhoto($this->jid, $size);
    }

    public function getTruenameAttribute()
    {
        if ($this->name && !filter_var($this->name, FILTER_VALIDATE_EMAIL)) {
            return $this->name;
        }
        if ($this->contact && $this->contact->truename) {
            return $this->contact->truename;
        }

        return explodeJid($this->jid)['username'] ?? $this->jid;
    }
}

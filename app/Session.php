<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session as MemorySession;

class Session extends Model
{
    protected $fillable = ['id'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence');
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'user_id')
                    ->where('resource', $this->resource)
                    ->where('session_id', $this->id);
    }

    public function contacts()
    {
        return $this->hasMany('App\Roster')->orderBy('jid');
    }

    public function conferences()
    {
        return $this->hasMany('App\Conference')->orderBy('conference');
    }

    public function init($username, $password, $host)
    {
        $this->id          = SESSION_ID;
        $this->host        = $host;
        $this->username    = $username;
        $this->user_id     = $username . '@' . $host;
        $this->resource    = 'movim' . \generateKey(6);
        $this->hash        = sha1($this->username . $password . $this->host);
        $this->active      = false;

        // TODO Cleanup
        $s = MemorySession::start();
        $s->set('password', $password);
    }

    public function getUploadService()
    {
        return Capability::where('node', 'like', '%' . $this->host . '%')
                              ->where('features', 'like', '%urn:xmpp:http:upload%')
                              ->first();
    }

    public function getChatroomsService()
    {
        return Capability::where('node', 'like', '%' . $this->host . '%')
                              ->where('node', 'not like', '%@%')
                              ->where('category', 'conference')
                              ->first();
    }

    public function getCommentsService()
    {
        return Capability::where('node', 'comments.' . $this->host)
                              ->where('category', 'pubsub')
                              ->where('type', 'service')
                              ->first();
    }

    public function loadMemory()
    {
        $s = MemorySession::start();
        $s->set('jid',      $this->user_id);
        $s->set('host',     $this->host);
        $s->set('username', $this->username);
        $s->set('hash',     $this->hash);
        $s->set('active',   $this->active);
        $s->set('resource', $this->resource);
    }
}

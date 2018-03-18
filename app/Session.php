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

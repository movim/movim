<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session as MemorySession;

class Session extends Model
{
    protected $fillable = ['id'];

    public function init($username, $password, $host)
    {
        $this->id          = SESSION_ID;
        $this->host        = $host;
        $this->username    = $username;
        $this->resource    = 'movim'.\generateKey(6);
        $this->hash        = sha1($this->username . $password . $this->host);
        $this->active      = false;

        $s = MemorySession::start();
        $s->set('username', $this->username);
        $s->set('host',     $this->host);
        $s->set('password', $password);
        $s->set('jid',      $this->getJidAttribute());
        $s->set('hash',     $this->hash);
        $s->set('active',   $this->active);
    }

    public function getJidAttribute()
    {
        return $this->attributes['username'] . '@' . $this->attributes['host'];;
    }
}

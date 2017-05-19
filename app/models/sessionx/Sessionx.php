<?php

namespace Modl;

use Movim\Session;

class Sessionx extends Model
{
    public $session;
    public $username;
    public $hash;
    public $jid;
    public $resource;
    public $host;
    public $active;
    public $start;
    public $timestamp;

    public $_struct = [
        'session'   => ['type' => 'string','size' => 32,'key' => true],
        'jid'       => ['type' => 'string','size' => 64],
        'username'  => ['type' => 'string','size' => 64],
        'hash'      => ['type' => 'string','size' => 64],
        'resource'  => ['type' => 'string','size' => 16],
        'host'      => ['type' => 'string','size' => 64,'mandatory' => true],
        'active'    => ['type' => 'int','size' => 4],
        'start'     => ['type' => 'date'],
        'timestamp' => ['type' => 'date']
    ];

    public function init($user, $password, $host)
    {
        $this->session     = SESSION_ID;
        $this->host        = $host;
        $this->username    = $user;
        $this->jid         = $user.'@'.$host;
        $this->password    = $password;
        $this->resource    = 'moxl'.\generateKey(6);
        $this->start       = date(SQL::SQL_DATE);
        $this->hash        = sha1($this->username.$this->password.$this->host);
        $this->active      = 0;
        $this->timestamp   = date(SQL::SQL_DATE);
    }

    public function loadMemory()
    {
        $s = Session::start();
        $s->set('password', $this->password);
        $s->set('username', $this->username);
        $s->set('host',     $this->host);
        $s->set('jid',      $this->jid);
        $s->set('hash',     $this->hash);
        $s->set('active',   $this->active);
    }
}

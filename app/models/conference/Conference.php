<?php

namespace modl;

class Conference extends Model
{
    public $jid;
    public $conference;
    public $name;
    public $nick;
    public $autojoin;
    public $status;

    public $connected = false;

    public $_struct = [
        'jid'           => ['type' => 'string','size' => 128,'key' => true],
        'conference'    => ['type' => 'string','size' => 128,'key' => true],
        'name'          => ['type' => 'string','size' => 128,'mandatory' => true],
        'nick'          => ['type' => 'string','size' => 128],
        'autojoin'      => ['type' => 'int','size' => 1],
        'status'        => ['type' => 'int','size' => 1],
    ];
}

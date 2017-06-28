<?php

namespace Modl;

use \Modl\InfoDAO;
use \Modl\PresenceDAO;

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
        'autojoin'      => ['type' => 'bool'],
        'status'        => ['type' => 'bool'],
    ];

    public function getItem()
    {
        $id = new InfoDAO;
        return $id->getJid($this->conference);
    }

    public function countConnected()
    {
        $pd = new PresenceDAO;
        return $pd->countJid($this->conference);
    }
}

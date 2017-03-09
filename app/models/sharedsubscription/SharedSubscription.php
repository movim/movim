<?php

namespace Modl;

class SharedSubscription extends Model
{
    public $jid;
    public $server;
    public $node;
    public $title;

    public $_struct = [
        'jid'       => ['type' => 'string', 'size' => 64, 'key' => true],
        'server'    => ['type' => 'string', 'size' => 64, 'key' => true],
        'node'      => ['type' => 'string', 'size' => 128, 'key' => true],
        'title'     => ['type' => 'string', 'size' => 128]
    ];

    function set($jid, $item)
    {
        $this->jid          = $jid;
        $this->server       = (string)$item->attributes()->server;
        $this->node         = (string)$item->attributes()->node;
        $this->title        = (string)$item->title;
    }
}

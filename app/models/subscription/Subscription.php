<?php

namespace Modl;

use Movim\Picture;

class Subscription extends Model
{
    public $jid;
    public $server;
    public $node;
    public $subscription;
    public $subid;
    public $title;
    public $description;
    public $tags;
    public $timestamp;
    public $name;
    public $servicename;
    public $logo;

    public $_struct = [
        'jid'       => ['type' => 'string', 'size' => 64, 'key' => true],
        'server'    => ['type' => 'string', 'size' => 64, 'key' => true],
        'node'      => ['type' => 'string', 'size' => 128, 'key' => true],
        'subscription' => ['type' => 'serialized', 'size' => 128, 'mandatory' => true],
        'subid'     => ['type' => 'string', 'size' => 128],
        'title'     => ['type' => 'string', 'size' => 128],
        'tags'      => ['type' => 'serialized'],
        'timestamp' => ['type' => 'date',]
    ];

    public function getLogo()
    {
        $p = new Picture;
        return $p->get($this->server.$this->node, 120);
    }

    function set($jid, $server, $node, $s)
    {
        $this->jid          = $jid;
        $this->server       = $server;
        $this->node         = $node;
        $this->jid          = (string)$s->attributes()->jid;
        $this->subscription = (string)$s->attributes()->subscription;
        $this->subid        = (string)$s->attributes()->subid;
        $this->tags         = [];

        if($this->subid = '') {
            $this->subid = 'default';
        }
    }
}

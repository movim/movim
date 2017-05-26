<?php

namespace Modl;

class Invite extends Model
{
    public $code;
    public $jid;
    public $resource;
    public $created;

    public $_struct = [
        'code'      => ['type' => 'string','size' => 8,'mandatory' => true,'key' => true],
        'jid'       => ['type' => 'string','size' => 64,'mandatory' => true],
        'resource'  => ['type' => 'string','size' => 128,'mandatory' => true],
        'created'   => ['type' => 'date','mandatory' => true],
    ];

    static function set($jid, $resource)
    {
        $id = new InviteDAO;
        $i = $id->getCode($jid, $resource);

        if($i == null) {
            $i = new Invite;
            $i->code        = generateKey(8);
            $i->jid         = $jid;
            $i->resource    = $resource;

            $id->set($i);
        }

        return $i;
    }

    static function get($code)
    {
        $id = new InviteDAO;
        return $id->get($code);
    }
}

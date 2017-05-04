<?php

namespace Modl;

class EncryptedPass extends Model
{
    public $session;
    public $id;
    public $data;
    public $timestamp;

    public $_struct = [
        'session'   => ['type' => 'string','size' => 64,'key' => true],
        'id'        => ['type' => 'string','size' => 32,'key' => true],
        'data'      => ['type' => 'text','mandatory' => true],
        'timestamp' => ['type' => 'date','mandatory' => true]
    ];
}

<?php

namespace Modl;

class Privacy extends Model
{
    public $pkey;
    public $value;
    public $hash;

    public $_struct = [
        'pkey'  => ['type' => 'string','size' => 128,'mandatory' => true,'key' => true],
        'value' => ['type' => 'bool','mandatory' => true]
    ];

    static function set($key, $value)
    {
        $p = new Privacy;
        $p->pkey  = $key;
        $p->value = $value;

        $pd = new PrivacyDAO;
        $pd->set($p);
    }

    static function get($key)
    {
        $pd = new PrivacyDAO;
        return (bool)$pd->get($key)->value;
    }
}

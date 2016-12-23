<?php

namespace Modl;

class Tag extends Model
{
    public $tag;
    public $nodeid;

    public $_struct = [
        'tag'       => ['type' => 'string', 'size' => 64, 'mandatory' => true, 'key' => true],
        'nodeid'    => ['type' => 'string', 'size' => 96, 'mandatory' => true, 'key' => true]
    ];
}

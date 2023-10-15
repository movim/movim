<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Affiliation extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $primaryKey = ['server', 'node', 'jid'];
    public $incrementing = false;
}

<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;

class Affiliation extends Model
{
    public $primaryKey = ['server', 'node', 'jid'];
    public $incrementing = false;
}

<?php

namespace App;

use Movim\Model;

class Affiliation extends Model
{
    public $primaryKey = ['server', 'node', 'jid'];
    public $incrementing = false;
}

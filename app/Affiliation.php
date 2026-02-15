<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;

class Affiliation extends Model
{
    public $primaryKey = ['server', 'node', 'jid'];
    public $incrementing = false;

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id', 'jid');
    }

    public function getAffiliationtextAttribute(): string
    {
        return getAffiliations()[$this->affiliation];
    }
}

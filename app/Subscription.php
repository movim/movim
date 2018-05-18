<?php

namespace App;

use Movim\Model;

class Subscription extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['jid', 'server', 'node'];
    protected $guarded = [];

    public function info()
    {
        return $this->hasOne('App\Info', 'server', 'server')
                    ->where('node', $this->node);
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }
}

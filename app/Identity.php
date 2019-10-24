<?php

namespace App;

use Movim\Model;

class Identity extends Model
{
    protected $primaryKey = ['info_id', 'category', 'type'];
    public $incrementing = false;

    public function info()
    {
        return $this->belongsTo('App\Info');
    }
}

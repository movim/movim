<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;

class MAMEarliest extends Model
{
    protected $table = 'mam_earliest';

    use \Awobaz\Compoships\Compoships;

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}

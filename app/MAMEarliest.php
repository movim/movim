<?php

namespace App;

use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Model;

class MAMEarliest extends Model
{
    protected $table = 'mam_earliest';

    use Compoships;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

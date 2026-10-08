<?php

namespace App;

use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Model;

class MAMEarliest extends Model
{
    use Compoships;

    protected $table = 'mam_earliest';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

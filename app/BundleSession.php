<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BundleSession extends Model
{
    protected $table = 'bundle_sessions';

    public function bundle()
    {
        return $this->belongsTo('App\Bundle', 'id', 'bundle_id');
    }
}
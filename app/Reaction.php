<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    protected $primaryKey = 'message_mid';

    public function message()
    {
        return $this->belongsTo('App\Message');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jidfrom');
    }
}

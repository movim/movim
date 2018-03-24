<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EncryptedPassword extends Model
{
    protected $fillable = ['id'];
    public $incrementing = false;

    public function session()
    {
        return $this->hasOne('App\Session');
    }
}

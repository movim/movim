<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['id', 'language', 'nightmode', 'nsfw', 'cssurl'];
    public $incrementing = false;

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id');
    }

    public function setPublic()
    {
        $this->attributes['public'] = true;
        $this->save();
    }

    public function setPrivate()
    {
        $this->attributes['public'] = false;
        $this->save();
    }
}

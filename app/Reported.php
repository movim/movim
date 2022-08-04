<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reported extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'reported';

    public $fillable = ['id'];

    public function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }
}
<?php

namespace App;

use Movim\Model;

class Reported extends Model
{
    protected $keyType = 'string';
    protected $table = 'reported';

    public $fillable = [
        'id'
    ];

    public function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }
}
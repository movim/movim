<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function post()
    {
        return $this->belongsTo('App\Post');
    }

    public function getUrlAttribute()
    {
        return parse_url($this->href);
    }
}

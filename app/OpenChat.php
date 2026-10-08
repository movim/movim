<?php

namespace App;

use Movim\Model;

class OpenChat extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['user_id', 'jid'];
    protected $fillable = ['jid'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

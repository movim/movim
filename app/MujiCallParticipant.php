<?php

namespace App;

use Movim\Image;
use Movim\Model;

class MujiCallParticipant extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'muji_call_id', 'jid'];
    protected $fillable = ['session_id', 'muji_call_id', 'jid', 'left_at'];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function mujiCall()
    {
        return $this->belongsTo('App\MujiCall', 'id', 'muji_call_id')

        ->where('session_id', $this->session_id);
    }

    public function getConferencePictureAttribute(): string
    {
        return Image::getOrCreate($this->jid, 120) ?? avatarPlaceholder($this->jid . 'groupchat');
    }
}

<?php

namespace App;

use Movim\Image;
use Movim\Model;

class MujiCallParticipant extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'muji_call_id', 'jid'];
    protected $fillable = ['session_id', 'muji_call_id', 'jid', 'left_at', 'inviter'];

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

    public function getMeAttribute(): bool
    {
        $jid = explodeJid($this->jid);

        if ($jid['resource'] == null) {
            return $this->jid == me()->id;
        }

        $presence = Presence::where('jid', $jid['jid'])->where('resource', $jid['resource'])->first();

        return $presence && $presence->mucjid == me()->id;
    }

    public function getNameAttribute()
    {
        return explodeJid($this->jid)['resource'];
    }

    public function getConferencePictureAttribute(): string
    {
        return Image::getOrCreate($this->jid, 120) ?? avatarPlaceholder($this->jid);
    }
}

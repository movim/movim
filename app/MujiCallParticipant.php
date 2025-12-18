<?php

namespace App;

use Movim\Image;
use Movim\Model;

class MujiCallParticipant extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $incrementing = false;
    protected $primaryKey = ['session_id', 'muji_call_id', 'jid'];
    protected $fillable = ['session_id', 'muji_call_id', 'jid', 'left_at', 'inviter'];

    public function session()
    {
        return $this->hasOne(Session::class);
    }

    public function mujiCall()
    {
        return $this->belongsTo(MujiCall::class, ['id', 'session_id'], ['muji_call_id', 'session_id']);
    }

    public function isUser(User $user): bool
    {
        $jid = explodeJid($this->jid);

        if ($jid['resource'] == null) {
            return $this->jid == $user->id;
        }

        $presence = Presence::where('jid', $jid['jid'])->where('resource', $jid['resource'])->first();

        return $presence && $presence->mucjid == $user->id;
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

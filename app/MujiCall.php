<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;

class MujiCall extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $incrementing = false;
    protected $primaryKey = ['session_id', 'id'];
    protected $fillable = ['session_id', 'id', 'muc', 'jidfrom', 'video', 'isfromconference'];
    protected $with = ['participants', 'presences'];

    public function session()
    {
        return $this->hasOne(Session::class);
    }

    public function conference()
    {
        return $this->hasOne(Conference::class, ['conference', 'session_id'], ['jidfrom', 'session_id']);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class, ['jid', 'session_id'], ['muc', 'session_id'])
            ->where('value', '<', '5')
            ->where('resource', '!=', '');
    }

    public function participants()
    {
        return $this->hasMany(MujiCallParticipant::class, ['muji_call_id', 'session_id'], ['id', 'session_id']);
    }

    public function inviter()
    {
        return $this->hasOne(MujiCallParticipant::class, ['muji_call_id', 'session_id'], ['id', 'session_id'])
            ->where('inviter', true);
    }

    public function getJoinedAttribute(): bool
    {
        return linker($this->session_id)->currentCall->isJidInCall($this->jidfrom)
            && linker($this->session_id)->currentCall->mujiRoom == $this->muc;
    }

    public function getIconAttribute()
    {
        return $this->video ? 'videocam' : 'call';
    }

    public function getendIconAttribute()
    {
        return $this->video ? 'videocam_off' : 'call_end';
    }
}

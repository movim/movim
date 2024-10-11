<?php

namespace App;

use Movim\Model;

class MujiCall extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'conference'];
    protected $fillable = ['id', 'muc', 'conference_id', 'video'];
    protected $with = ['participants'];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function conference()
    {
        return $this->hasOne('App\Conference', 'conference', 'conference_id')
            ->where('session_id', $this->session_id);
    }

    public function participants()
    {
        return $this->hasMany('App\MujiCallParticipant', 'muji_call_id', 'id')
            ->where('session_id', $this->session_id);
    }

    public function getIconAttribute()
    {
        return $this->video ? 'videocam' : 'call';
    }

    /*public function presences()
    {
        return $this->hasMany('App\Presence', 'jid', 'conference')
                    ->where('session_id', $this->session_id)
                    ->where('resource', '!=', '')
                    ->where('value', '<', 5)
                    ->orderBy('mucrole')
                    ->orderBy('mucaffiliation', 'desc')
                    ->orderBy('value')
                    ->orderBy('resource');
    }*/
}

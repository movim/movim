<?php

namespace App;

use Movim\Model;
use Movim\Picture;

class Conference extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'conference'];
    protected $fillable = ['conference', 'name', 'nick', 'autojoin'];
    protected $with = ['contact'];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence', 'jid', 'conference')
                    ->where('session_id', $this->session_id)
                    ->where('resource', '!=', '')
                    ->where('value', '<', 5)
                    ->orderBy('mucrole')
                    ->orderBy('mucaffiliation', 'desc')
                    ->orderBy('value')
                    ->orderBy('resource');
    }

    public function unreads()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
                    ->where('user_id', \App\User::me()->id)
                    ->where('type', 'groupchat')
                    ->where('seen', false);
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'conference')
                    ->where('session_id', $this->session_id)
                    ->where('value', '<', 5)
                    ->where('mucjid', \App\User::me()->id);
    }

    public function info()
    {
        return $this->hasOne('App\Info', 'server', 'conference')
                    ->where('category', 'conference')
                    ->where('type', 'text');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'conference');
    }

    public function getServerAttribute()
    {
        return \explodeJid($this->conference)['server'];
    }

    public function getConnectedAttribute()
    {
        return isset($this->presence);
    }

    public function getSubjectAttribute()
    {
        $subject = \App\User::me()
                            ->messages()
                            ->where('jidfrom', $this->conference)
                            ->whereNotNull('subject')
                            ->where('type', 'subject')
                            ->orderBy('published', 'desc')
                            ->first();

        return $subject ? $subject->subject : null;
    }

    public function getPhoto($size = 'm')
    {
        if ($this->contact) {
            return $this->contact->getPhoto($size);
        }

        return false;
    }
}

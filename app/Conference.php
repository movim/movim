<?php

namespace App;

use Movim\Model;
use Movim\Picture;

class Conference extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'conference'];
    protected $fillable = ['conference', 'name', 'nick', 'autojoin'];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public function save(array $options = [])
    {
        try {
            parent::save($options);
        } catch (\Exception $e) {
            /*
             * Race condition
             */
        }
    }

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence', 'jid', 'conference')
                    ->where('session_id', $this->session_id)
                    ->where('resource', '!=', '')
                    ->orderBy('mucaffiliation', 'desc')
                    ->orderBy('value');
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'conference')
                    ->where('session_id', $this->session_id)
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

    public function getConnectedAttribute()
    {
        return isset($this->presence);
    }

    public function getSubjectAttribute()
    {
        $subject = \App\Message::where('jidfrom', $this->conference)
                               ->whereNotNull('subject')
                               ->where('type', 'groupchat')
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

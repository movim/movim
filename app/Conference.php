<?php

namespace App;

use CoenJacobs\EloquentCompositePrimaryKeys\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Movim\Picture;

class Conference extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    protected $primaryKey = ['session_id', 'conference'];
    protected $fillable = ['conference', 'name', 'nick', 'autojoin'];

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

    public function setAvatar($vcard)
    {
        if ($vcard->vCard->PHOTO->BINVAL) {
            $p = new \Movim\Picture;
            $p->fromBase((string)$vcard->vCard->PHOTO->BINVAL);
            $p->set($this->conference . '_muc');
        }
    }

    public function getConnectedAttribute()
    {
        if (!$this->nick) {
            $session = \Movim\Session::start();
            $resource = $session->get('username');
        } else {
            $resource = $this->nick;
        }

        return ($this->presences->where('mucjid', \App\User::me()->id)->count() > 0
             || $this->presences->where('resource', $resource)->count() > 0);
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

    public function getPhoto($size = 'l')
    {
        $sizes = [
            'l'     => [210 , false],
            'm'     => [120 , false],
            's'     => [50  , false],
            'xs'    => [28  , false],
            'xxs'   => [24  , false]
        ];

        $p = new Picture;
        return $p->get($this->conference, $sizes[$size][0], $sizes[$size][1]);
    }
}

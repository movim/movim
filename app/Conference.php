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
                    ->where('session_id', $this->session_id);
    }

    public function setAvatar($vcard)
    {
        if ($vcard->vCard->PHOTO->BINVAL) {
            $p = new \Movim\Picture;
            $p->fromBase((string)$vcard->vCard->PHOTO->BINVAL);
            $p->set($this->conference . '_muc');
        }
    }

    public function getItem()
    {
        $id = new \Modl\InfoDAO;
        return $id->getJid($this->conference);
    }

    public function getConnectedAttribute()
    {
        if (!$this->nick) {
            $session = \Movim\Session::start();
            $resource = $session->get('username');
        } else {
            $resource = $this->nick;
        }

        return ($this->presences->where('mucjid', \App\User::me()->id)->first()
             || $this->presences->where('resource', $resource)->first());
    }

    public function getPhoto($size = 'l')
    {
        $sizes = [
            'm'     => [120 , false],
            's'     => [50  , false],
            'xs'    => [28  , false],
            'xxs'   => [24  , false]
        ];

        $p = new Picture;
        return $p->get($this->conference . '_muc', $sizes[$size][0], $sizes[$size][1]);
    }
}

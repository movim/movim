<?php

namespace App;

use Movim\Model;

class Conference extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'conference'];
    protected $fillable = ['conference', 'name', 'nick', 'autojoin'];
    protected $with = ['contact'];

    public static $xmlns = 'xmpp:movim.eu/notifications:0';
    public static $notifications = [
        0 => 'never',
        1 => 'quoted',
        2 => 'always'
    ];

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
                    ->whereNull('subject')
                    ->where('seen', false);
    }

    public function quoted()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
                    ->where('user_id', \App\User::me()->id)
                    ->where('type', 'groupchat')
                    ->whereNull('subject')
                    ->where('quoted', true)
                    ->where('seen', false);
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'conference')
                    ->where('session_id', $this->session_id)
                    ->where('value', '<', 5)
                    ->where('mucjid', \App\User::me()->id);
    }

    public function pictures()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
                    ->where('user_id', \App\User::me()->id)
                    ->where('type', 'groupchat')
                    ->where('picture', true)
                    ->orderBy('published', 'desc');
    }

    public function info()
    {
        return $this->hasOne('App\Info', 'server', 'conference')
                    ->where('node', '')
                    ->whereCategory('conference')
                    ->whereType('text');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'conference');
    }

    public function set($item)
    {
        $this->conference      = (string)$item->attributes()->id;
        $this->name            = (string)$item->conference->attributes()->name;
        $this->nick            = (string)$item->conference->nick;
        $this->autojoin        = filter_var($item->conference->attributes()->autojoin, FILTER_VALIDATE_BOOLEAN);
        $this->bookmarkversion = (int)substr((string)$item->conference->attributes()->xmlns, -1, 1);

        if ($item->conference->extensions) {
            if ($item->conference->extensions && $item->conference->extensions->notifications
            && $item->conference->extensions->notifications->attributes()->xmlns == self::$xmlns) {
                $this->notify = (int)array_flip(self::$notifications)[
                    (string)$item->conference->extensions->notifications->attributes()->notify
                ];
                unset($item->conference->extensions->notifications);
            }

            $this->extensions = $item->conference->extensions->asXML();
        }
    }

    public function getServerAttribute()
    {
        return \explodeJid($this->conference)['server'];
    }

    public function getConnectedAttribute()
    {
        return isset($this->presence);
    }

    public function getNotificationKeyAttribute()
    {
        return self::$notifications[$this->notify];
    }

    public function getSubjectAttribute()
    {
        $subject = \App\User::me()
                            ->messages()
                            ->where('jidfrom', $this->conference)
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

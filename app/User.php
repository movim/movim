<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session;
use App\Contact;

class User extends Model
{
    protected $fillable = ['id', 'language', 'nightmode', 'nsfw', 'cssurl'];
    public $with = ['session', 'capability'];
    public $incrementing = false;
    private static $me = null;

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id');
    }

    public function capability()
    {
        return $this->hasOne('App\Capability', 'node', 'id');
    }

    public function messages()
    {
        return $this->hasMany('App\Message');
    }

    public function encryptedPasswords()
    {
        return $this->hasMany('App\EncryptedPassword');
    }

    public function subscriptions()
    {
        return $this->hasMany('App\Subscription', 'jid', 'id');
    }

    public static function me($reload = false)
    {
        $session = Session::start();

        if (self::$me != null
        && self::$me->id == $session->get('jid')
        && $reload == false) {
            return self::$me;
        }

        $me = self::find($session->get('jid'));
        self::$me = $me;

        return ($me) ? $me : new User;
    }

    public function isLogged()
    {
        return (bool)(Session::start())->get('jid');
    }

    public function getJidAttribute()
    {
        return (Session::start())->get('jid');
    }

    /* Temporary */
    public function getServer()
    {
        return (Session::start())->get('host');
    }

    public function init()
    {
        $contact = Contact::firstOrNew(['id' => $this->id]);
        $contact->save();
    }

    public function setConfig(array $config)
    {
        if (isset($config['language'])) {
            $this->language = $config['language'];
        }

        if (isset($config['cssurl'])) {
            $this->cssurl = $config['cssurl'];
        }

        if (isset($config['nsfw'])) {
            $this->nsfw = $config['nsfw'];
        }

        if (isset($config['nightmode'])) {
            $this->nightmode = $config['nightmode'];
        }

        // Reload the user
        self::me(true);
        (new \Movim\Bootstrap)->loadLanguage();
    }

    public function hasMAM()
    {
        return ($this->capability && $this->capability->isMAM2());
    }

    public function hasPubsub()
    {
        return ($this->capability && $this->capability->isPubsub());
    }

    public function hasUpload()
    {
        return ($this->session && $this->session->getUploadService());
    }

    public function setPublic()
    {
        $this->attributes['public'] = true;
        $this->save();
    }

    public function setPrivate()
    {
        $this->attributes['public'] = false;
        $this->save();
    }
}

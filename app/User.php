<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session;
use App\Contact;

class User extends Model
{
    protected $fillable = ['id', 'language', 'nightmode', 'nsfw', 'cssurl'];
    public $incrementing = false;

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

    public static function me()
    {
        $session = Session::start();
        $me = self::find($session->get('jid'));

        return ($me) ? $me : new User;
    }

    public function getJidAttribute()
    {
        return (Session::start())->get('jid');
    }

    /* Temporary */
    public function getLogin()
    {
        return $this->getJidAttribute();
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

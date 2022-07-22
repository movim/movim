<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configuration';

    private static $instance = null;

    public $fillable = [
        'description',
        'disableregistration',
        'info',
        'unregister',
        'gifapikey',
        'restrictsuggestions',
        'locale',
        'loglevel',
        'username',
        'password',
        'twittertoken',
        'xmppdomain',
        'xmppdescription',
        'xmppwhitelist'
    ];

    protected $attributes = [
        'id'                    => 1,
        'unregister'            => false,
        'disableregistration'   => false,
        'restrictsuggestions'   => false,
        'loglevel'              => 0,
        'locale'                => 'en',
        'xmppwhitelist'         => null
    ];

    public static function get()
    {
        if (self::$instance != null) {
            return self::$instance;
        }

        self::$instance = self::findOrNew(1);
        return self::$instance;
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    public function getXmppwhitelistAttribute()
    {
        return (empty($this->attributes['xmppwhitelist']))
            ? []
            : explode(',', $this->attributes['xmppwhitelist']);
    }

    public function getXmppwhitelistStringAttribute()
    {
        return $this->attributes['xmppwhitelist'];
    }
}

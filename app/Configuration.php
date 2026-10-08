<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configuration';

    private static $instance = null;

    public $fillable = [
        'chatonly',
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
        'ssrfwhitelist',
        'xmppdomain',
        'xmppdescription',
        'xmppwhitelist',
    ];

    protected $attributes = [
        'id'                    => 1,
        'unregister'            => false,
        'disableregistration'   => false,
        'chatonly'              => false,
        'restrictsuggestions'   => false,
        'loglevel'              => 0,
        'locale'                => 'en',
        'xmppwhitelist'         => null,
        'ssrfwhitelist'         => null,
        'gifapikey'             => null,
        'maxsessions'           => 0,
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

    public function getXmppwhitelistAttribute(): array
    {
        return (empty($this->attributes['xmppwhitelist']))
            ? []
            : array_map(fn ($domain) => trim($domain), explode(',', $this->attributes['xmppwhitelist']));
    }

    public function getSsrfwhitelistArrayAttribute(): array
    {
        return (empty($this->attributes['ssrfwhitelist']))
            ? []
            : array_map(fn ($domain) => trim($domain), explode(',', $this->attributes['ssrfwhitelist']));
    }

    public function getXmppwhitelistStringAttribute()
    {
        return $this->attributes['xmppwhitelist'];
    }
}

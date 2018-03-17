<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $table = 'configuration';

    public $fillable = [
        'description',
        'info',
        'unregister',
        'restrictsuggestions',
        'theme',
        'locale',
        'loglevel',
        'username',
        'password',
        'xmppdomain',
        'xmppdescription',
        'xmppcountry',
        'xmppwhitelist'
    ];

    protected $attributes = [
        'id'                    => 1,
        'unregister'            => false,
        'theme'                 => 'material',
        'restrictsuggestions'   => false,
        'loglevel'              => 0,
        'locale'                => 'en',
        'xmppwhitelist'         => null
    ];

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

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator;
use Movim\EmbedLight;
use Embed\Http\CurlDispatcher;

class Url extends Model
{
    protected $primaryKey = 'hash';
    protected $keyType = 'string';
    public $incrementing = false;

    public static function resolve($url)
    {
        if (Validator::url()->validate($url)) {
            $hash = hash('sha256', $url);
            $cached = \App\Url::find($hash);

            if ($cached) {
                return $cached->cache;
            } else {
                $cached = new \App\Url;
                $cached->hash = $hash;
            }

            $cached->cache = $url;
            $cached->save();

            return $cached->cache;
        }
    }

    public function getCacheAttribute()
    {
        return unserialize(base64_decode($this->attributes['cache']));
    }

    public function setCacheAttribute($url)
    {
        $dispatcher = new CurlDispatcher([
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 5
        ]);

        $embed = new EmbedLight(\Embed\Embed::create($url, null, $dispatcher));
        $this->attributes['cache'] = base64_encode(serialize($embed));
    }
}

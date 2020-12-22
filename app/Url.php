<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator;
use Movim\EmbedLight;
use Embed\Http\CurlDispatcher;

class Url extends Model
{
    public static $id = 0;

    public static function resolve($url)
    {
        if (Validator::url()->validate($url)) {
            $hash = hash('sha256', $url);
            $cached = \App\Url::where('hash', $hash)->first();

            if ($cached) {
                self::$id = $cached->id;
                return $cached->cache;
            } else {
                $cached = new \App\Url;
                $cached->hash = $hash;
            }

            $cached->cache = $url;
            $cached->save();

            self::$id = $cached->id;

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
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => DEFAULT_HTTP_USER_AGENT,
        ]);

        $embed = new EmbedLight(\Embed\Embed::create($url, null, $dispatcher));
        $this->attributes['cache'] = base64_encode(serialize($embed));
    }
}

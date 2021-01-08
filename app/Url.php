<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator;
use Movim\EmbedLight;
use Embed\Http\CurlDispatcher;

class Url extends Model
{
    public function resolve($url)
    {
        if (Validator::url()->validate($url)) {
            $hash = hash('sha256', $url);
            $cached = \App\Url::where('hash', $hash)->first();

            if ($cached) {
                $this->id = $cached->id;
                $this->maybeResolveMessageFile($cached->cache);
                return $cached->cache;
            } else {
                $cached = new \App\Url;
                $cached->hash = $hash;
            }

            $cached->cache = $url;
            $cached->save();

            $this->id = $cached->id;
            $this->maybeResolveMessageFile($cached->cache);

            return $cached->cache;
        }
    }

    public function maybeResolveMessageFile($cache)
    {
        if ($cache->title == $cache->url
        && ((
                $cache->type == 'photo'
                && !empty($cache->images)
                && isset($cache->contentType)
                && typeIsPicture($cache->contentType)
            ) || (
                $cache->type == 'video'
                && isset($cache->contentType)
                && typeIsVideo($cache->contentType)
            ))
        ) {
            $name = '';
            $path = parse_url($cache->url, PHP_URL_PATH);
            if ($path) {
                $name = basename($path);
            }

            $file = new MessageFile;
            $file->name = !empty($name) ? $name : $cache->url;
            $file->type = $cache->contentType;
            $file->size = 20000;//$cache->images[0]['size'];
            $file->uri  = $cache->url;

            $this->file = $file;
        }
    }

    public function getCacheAttribute()
    {
        return unserialize(base64_decode($this->attributes['cache']));
    }

    public function setCacheAttribute($url)
    {
        $dispatcher = new CurlDispatcher([
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => DEFAULT_HTTP_USER_AGENT,
        ]);

        $embed = new EmbedLight(\Embed\Embed::create($url, null, $dispatcher));
        $this->attributes['cache'] = base64_encode(serialize($embed));
    }
}

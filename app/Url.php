<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator;
use Movim\EmbedLight;

use function React\Async\await;

class Url extends Model
{
    public ?MessageFile $file = null;

    public function resolve($url, bool $now = false)
    {
        if (Validator::url()->isValid($url)) {
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

            if ($now) return;

            try {
                $info = await(requestResolverWorker($url));
                $embed = new EmbedLight($info);
                $cached->cache = base64_encode(serialize($embed));
                $cached->save();

                $this->id = $cached->id;
                $this->maybeResolveMessageFile($embed);

                return $embed;
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }
    }

    public function maybeResolveMessageFile($cache)
    {
        if ($cache->title == $cache->url
        && ($cache->type == 'image' || $cache->type == 'video')
        ) {
            $name = '';
            $path = parse_url($cache->url, PHP_URL_PATH);
            if ($path) {
                $name = basename($path);
            }

            $this->file = new MessageFile;
            $this->file->name = !empty($name) ? $name : $cache->url;
            $this->file->type = $cache->contentType;
            $this->file->size = (!empty($cache->images[0]) && array_key_exists('size', $cache->images[0]))
                ? $cache->images[0]['size']
                : 20000;
            $this->file->url  = $cache->url;
        }
    }

    public function getCacheAttribute()
    {
        return unserialize(base64_decode($this->attributes['cache']));
    }
}

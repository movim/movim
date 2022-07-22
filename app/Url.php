<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator;
use Movim\EmbedLight;

use Embed\Embed;
use Embed\Http\Crawler;
use Embed\Http\CurlClient;
use Movim\EmbedImagesExtractor;

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
        && ($cache->type == 'image' || $cache->type == 'video')
        ) {
            $name = '';
            $path = parse_url($cache->url, PHP_URL_PATH);
            if ($path) {
                $name = basename($path);
            }

            $file = new MessageFile;
            $file->name = !empty($name) ? $name : $cache->url;
            $file->type = $cache->contentType;
            $file->size = (!empty($cache->images[0]) && array_key_exists('size', $cache->images[0]))
                ? $cache->images[0]['size']
                : 20000;
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
        $client = new CurlClient;
        $client->setSettings([
            'max_redirs' => 3,               // see CURLOPT_MAXREDIRS
            'connect_timeout' => 5,          // see CURLOPT_CONNECTTIMEOUT
            'timeout' => 5,                  // see CURLOPT_TIMEOUT
            'ssl_verify_host' => 2,          // see CURLOPT_SSL_VERIFYHOST
            'ssl_verify_peer' => 1,          // see CURLOPT_SSL_VERIFYPEER
            'follow_location' => true,       // see CURLOPT_FOLLOWLOCATION
            'user_agent' => DEFAULT_HTTP_USER_AGENT,
        ]);

        try {
            $embed = new Embed(new Crawler($client));
            $configuration = Configuration::get();

            if (!empty($configuration->twittertoken)) {
                $embed->setSettings([
                    'twitter:token' => $configuration->twittertoken
                ]);
            }

            $embed->getExtractorFactory()->addDetector('images', EmbedImagesExtractor::class);
            $info = $embed->get($url);

            $embed = new EmbedLight($info);

            $this->attributes['cache'] = base64_encode(serialize($embed));
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}

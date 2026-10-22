<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use React\Promise\Deferred;
use React\Promise\Promise;
use Respect\Validation\Validator;

class Url extends Model
{
    private const HASH_METHOD = 'sha256';

    public static function resolveFromDB(?string $url): ?Url
    {
        if (Validator::url()->isValid($url)) {
            $hash = hash(self::HASH_METHOD, $url);
            $dbUrl = Url::where('hash', $hash)->first();

            if ($dbUrl) {
                return $dbUrl;
            }
        }

        return null;
    }

    public static function resolve(?string $url, ?int $timeout = 10): Promise
    {
        $defered = new Deferred;

        if (! Validator::url()->isValid($url)) {
            $defered->reject('Bad URL');
        }

        if ($dbUrl = self::resolveFromDB($url)) {
            $defered->resolve($dbUrl);
        }

        try {
            requestResolverWorker($url, $timeout)->then(function ($resolved) use ($defered, $url) {
                $dbUrl = new Url;
                $dbUrl->author_name = $resolved->authorName;
                $dbUrl->author_url = $resolved->authorUrl;
                $dbUrl->content_type = $resolved->contentType;
                $dbUrl->content_length = $resolved->contentLength ?? 0;
                $dbUrl->description = $resolved->description;
                $dbUrl->hash = hash(self::HASH_METHOD, $url);
                $dbUrl->image = $resolved->image;
                $dbUrl->provider_icon = $resolved->icon;
                $dbUrl->provider_name = $resolved->providerName;
                $dbUrl->provider_url = $resolved->providerUrl;
                $dbUrl->published_at = $resolved->publishedTime;
                $dbUrl->title = $resolved->title;
                $dbUrl->type = $resolved->type;
                $dbUrl->url = $url;

                $dbUrl->images = $resolved->images;
                $dbUrl->tags = $resolved->keywords;

                $dbUrl->save();

                $defered->resolve($dbUrl);
            }, function () use ($defered) {
                $defered->reject('Timeout');
            });
        } catch (\Exception $e) {
            $defered->reject('Cannot resolve the URL');
        }

        return $defered->promise();
    }

    public function setTagsAttribute(array $tags)
    {
        $dbTags = [];

        foreach ($tags as $tag) {
            array_push($dbTags, (string) $tag);
        }

        $this->attributes['serialized_tags'] = serialize($dbTags);
    }

    public function getTagsAttribute(): array
    {
        return unserialize($this->attributes['serialized_tags']);
    }

    public function setImagesAttribute(array $images)
    {
        $dbImages = [];

        foreach ($images as $image) {
            $image = is_array($image) ? $image['url'] : $image; // hack
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                array_push($dbImages, [
                    'url' => (string) $image,
                    'size' => $image == $this->attributes['url']
                        ? $this->attributes['content_length']
                        : 0,
                ]);
            }
        }

        $this->attributes['serialized_images'] = serialize($dbImages);
    }

    public function getImagesAttribute(): array
    {
        return unserialize($this->attributes['serialized_images']);
    }

    public function getMessageFileAttribute(): ?MessageFile
    {
        if (
            $this->type == 'image' || $this->type == 'video'
        ) {
            $name = '';
            $path = parse_url($this->url, PHP_URL_PATH);
            if ($path) {
                $name = basename($path);
            }

            $file = new MessageFile;
            $file->name = $this->title ?? $name;
            $file->type = $this->content_type;
            $file->size = count($this->images) > 0 ? $this->content_length : 0;
            $file->url = $this->url;

            return $file;
        }

        return null;
    }
}

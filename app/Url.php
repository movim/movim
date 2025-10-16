<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Respect\Validation\Validator;
use function React\Async\await;

class Url extends Model
{
    public static function resolve(?string $url, bool $now = false): ?Url
    {
        if (Validator::url()->isValid($url)) {
            $hash = hash('sha256', $url);
            $dbUrl = \App\Url::where('hash', $hash)->first();

            if ($dbUrl) return $dbUrl;
            if ($now) return null;

            try {
                $resolved = await(requestResolverWorker($url));

                if ($resolved) {
                    $dbUrl = new Url;
                    $dbUrl->author_name = $resolved->authorName;
                    $dbUrl->author_url = $resolved->authorUrl;
                    $dbUrl->content_type = $resolved->contentType;
                    $dbUrl->content_length = $resolved->contentLength ?? 0;
                    $dbUrl->description = $resolved->description;
                    $dbUrl->hash = $hash;
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

                    return $dbUrl;
                }
            } catch (\Exception $e) {}
        }

        return null;
    }

    public function setTagsAttribute(array $tags)
    {
        $dbTags = [];

        foreach ($tags as $tag) {
            array_push($dbTags, (string)$tag);
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
                    'url' => (string)$image,
                    'size' => $image == $this->attributes['url']
                        ? $this->attributes['content_length']
                        : 0
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
            $file->url  = $this->url;

            return $file;
        }

        return null;
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Image;

class Emoji extends Model
{
    protected $table = 'emojis';

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function pack()
    {
        return $this->belongsTo(EmojisPack::class, 'name', 'pack');
    }

    public function getImagePathAttribute(): string
    {
        return PUBLIC_EMOJIS_PATH . '/' . $this->attributes['pack'] . '/' . $this->attributes['filename'];
    }

    public function getUrlAttribute(): string
    {
        $url = Image::getOrCreate($this->attributes['cache_hash']);

        // If the cache picture is not available, we recreate it
        if ($url == null) {
            $image = new Image;
            $image->fromPath($this->getImagePathAttribute());
            $image->setKey(hash(Image::$hash, file_get_contents($this->getImagePathAttribute())));
            $image->save();

            return Image::getOrCreate($this->attributes['cache_hash']);
        }

        return $url;
    }

    public function getAliasPlaceholderAttribute(): string
    {
        return slugify(str_replace($this->attributes['pack'], '', $this->attributes['name']));
    }
}

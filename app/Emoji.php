<?php

namespace App;

use Cocur\Slugify\Slugify;
use Illuminate\Database\Eloquent\Model;
use Movim\Image;

class Emoji extends Model
{
    protected $table = 'emojis';

    public function users()
    {
        return $this->belongsToMany('App\Users')->withTimestamps();
    }

    public function pack()
    {
        return $this->belongsTo('App\EmojisPack', 'name', 'pack');
    }

    public function getImageAttribute(): ?Image
    {
        $image = new Image;
        $image->setKey($this->attributes['cache_hash']);

        return $image;
    }

    public function getUrlAttribute(): string
    {
        return Image::getOrCreate($this->attributes['cache_hash']);
    }

    public function getAliasPlaceholderAttribute(): string
    {
        $slugify = new Slugify();
        return $slugify->slugify(str_replace($this->attributes['pack'], '', $this->attributes['name']));
    }
}

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

    public function getImagePathAttribute(): string
    {
        return PUBLIC_EMOJIS_PATH . '/' . $this->attributes['pack'] . '/' . $this->attributes['filename'];
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

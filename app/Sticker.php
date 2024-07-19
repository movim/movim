<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Image;

class Sticker extends Model
{
    public function pack()
    {
        return $this->belongsTo('App\StickersPack', 'name', 'pack');
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
}

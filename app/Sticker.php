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

    public function getImagePathAttribute(): string
    {
        return PUBLIC_STICKERS_PATH . '/' . $this->attributes['pack'] . '/' . $this->attributes['filename'];
    }

    public function getUrlAttribute(): string
    {
        return Image::getOrCreate($this->attributes['cache_hash']);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StickersPack extends Model
{
    protected $table = 'stickers_packs';
    public $primaryKey = 'name';
    protected $keyType = 'string';

    public function stickers()
    {
        return $this->hasMany(Sticker::class, 'pack', 'name');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmojisPack extends Model
{
    protected $table = 'emojis_packs';
    protected $with = ['emojis'];
    public $primaryKey = 'name';
    protected $keyType = 'string';

    public function emojis()
    {
        return $this->hasMany('App\Emoji', 'pack', 'name');
    }
}

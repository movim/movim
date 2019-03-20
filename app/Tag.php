<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name'];

    public static function firstOrCreateSafe(array $attributes, array $values = [])
    {
        try {
            return static::firstOrCreate($attributes, $values);
        } catch (\PDOException $e) {
            /*
             * When an article is received the related tags can be saved
             * simultaneously by different processes in the DB
             */
        }
    }

    public function posts()
    {
        return $this->belongsToMany('App\Post')->withTimestamps();
    }
}

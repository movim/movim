<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hat extends Model
{
    protected $fillable = ['uri', 'title', 'hue', 'presence_id'];

    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }

    public function toArray()
    {
        $now = \Carbon\Carbon::now();

        return [
            'uri' => $this->attributes['uri'],
            'title' => $this->attributes['title'],
            'hue' => $this->attributes['hue'] ?? null,
            'presence_id' => $this->attributes['presence_id'],
            'created_at' => $this->attributes['created_at'] ?? $now,
            'updated_at' => $this->attributes['updated_at'] ?? $now,
        ];
    }

    public function getColorAttribute(): string
    {
        return $this->hue
            ? hueToPalette($this->hue)
            : stringToColor($this->uri);
    }
}

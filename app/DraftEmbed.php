<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use React\Promise\Promise;

class DraftEmbed extends Model
{
    protected $table = 'embeds';
    protected $fillable = ['draft_id', 'url'];

    public function draft()
    {
        return $this->belongsTo(Draft::class);
    }

    public function getHTMLIdAttribute()
    {
        return cleanupId('embed' . $this->id);
    }

    public function resolveFromDB(): ?Url
    {
        return Url::resolveFromDB($this->url);
    }

    public function resolve(?int $timeout = 10): Promise
    {
        return Url::resolve($this->url, $timeout);
    }
}

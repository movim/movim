<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DraftEmbed extends Model
{
    protected $table = 'embeds';

    public function draft()
    {
        return $this->belongsTo(Draft::class);
    }

    public function getHTMLIdAttribute()
    {
        return cleanupId('embed'.$this->id);
    }

    public function resolve(?int $timeout = 30): ?Url
    {
        try {
            return Url::resolve($this->url, $timeout);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return null;
    }
}

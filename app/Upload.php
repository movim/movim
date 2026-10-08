<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    public $incrementing = false;
    protected $table = 'upload';
    protected $guarded = [];
    protected $primaryKey = 'id';

    public function setHeadersAttribute(?array $headers)
    {
        $this->attributes['headers'] = $headers ? serialize($headers) : null;
    }

    public function getHeadersAttribute(): ?array
    {
        return $this->attributes['headers'] ? unserialize($this->attributes['headers']) : null;
    }
}

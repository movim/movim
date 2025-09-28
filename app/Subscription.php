<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;

class Subscription extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['jid', 'server', 'node'];
    protected $guarded = [];

    public static function saveMany(array $subscriptions)
    {
        return Subscription::insert($subscriptions);
    }

    public function info()
    {
        return $this->hasOne('App\Info', ['server', 'node'], ['server', 'node']);
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }

    public function scopeNotComments($query)
    {
        return $query->where('node', 'not like', Post::COMMENTS_NODE . '/%');
    }

    public function toArray()
    {
        $now = \Carbon\Carbon::now();
        return [
            'jid' => $this->attributes['jid'] ?? null,
            'server' => $this->attributes['server']  ?? null,
            'node' => $this->attributes['node'] ?? null,
            'subid' => $this->attributes['subid'] ?? null,
            'title' => $this->attributes['title'] ?? null,
            'public' => $this->attributes['public'] ?? false,
            'created_at' => $this->attributes['created_at'] ?? $now,
            'updated_at' => $this->attributes['updated_at'] ?? $now,
        ];
    }
}

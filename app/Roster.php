<?php

namespace App;

use Movim\ImageSize;
use Movim\Model;

class Roster extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'jid'];
    protected $fillable = ['jid', 'name', 'ask', 'subscription', 'group'];
    public $with = ['contact', 'stories'];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public function upsert(): Roster
    {
        return parent::updateOrCreate([
            'session_id' => $this->session_id,
            'jid' => $this->jid
        ], $this->only(['name', 'ask', 'subscription', 'group']));
    }

    public static function saveMany(array $rosters)
    {
        $now = \Carbon\Carbon::now();
        $rosters = collect($rosters)->map(function (array $data) use ($now) {
            return array_merge([
                'created_at' => $now,
                'updated_at' => $now,
            ], $data);
        })->all();

        return Roster::insert($rosters);
    }

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'jid');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence', 'jid', 'jid')
            ->where('resource', '!=', '')
            ->where('session_id', $this->session_id);
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'jid')
            ->where('session_id', $this->session_id)
            ->orderBy('value');
    }

    public function stories()
    {
        return $this->hasMany('App\Post', 'server', 'jid')
            ->myStories()
            ->withOnly([])
            ->withCount('myViews');
    }

    public function getFirstUnseenStoryAttribute(): ?Post
    {
        return $this->stories->filter(function ($story) {
            return $story->my_views_count == 0;
        })->first() ?? $this->stories->first();
    }

    public function getStoriesSeenAttribute(): bool
    {
        return !($this->stories && $this->stories->contains(function ($story) {
            return $story->my_views_count == 0;
        }));
    }

    public function set($stanza): bool
    {
        $this->session_id = SESSION_ID;

        $this->jid = (string)$stanza->attributes()->jid;

        $this->name = (isset($stanza->attributes()->name)
            && !empty((string)$stanza->attributes()->name))
            ? (string)$stanza->attributes()->name
            : null;

        $this->ask = $stanza->attributes()->ask
            ? (string)$stanza->attributes()->ask
            : null;

        $this->subscription = $stanza->attributes()->subscription
            ? (string)$stanza->attributes()->subscription
            : null;

        $this->group = $stanza->group
            ? (string)$stanza->group
            : null;

        return (
            strlen($this->jid) < 256 &&
            ($this->name == null || strlen($this->name) < 256) &&
            ($this->group == null || strlen($this->group) < 256)
        );
    }

    public function getSearchTerms()
    {
        return cleanupId($this->jid) . '-' .
            cleanupId($this->group);
    }

    public function getPicture(ImageSize $size = ImageSize::M): string
    {
        return getPicture($this->jid, $this->truename, $size);
    }

    public function getBanner(ImageSize $size = ImageSize::XXL)
    {
        $banner = !empty($this->id) ? getPicture($this->id . '_banner', $this->truename, $size) : null;

        return $banner == null ? $this->getPicture($size) : $banner;
    }

    public function getTruenameAttribute()
    {
        if ($this->name && !filter_var($this->name, FILTER_VALIDATE_EMAIL)) {
            return $this->name;
        }
        if ($this->contact && $this->contact->truename) {
            return $this->contact->truename;
        }

        return explodeJid($this->jid)['username'] ?? $this->jid;
    }
}

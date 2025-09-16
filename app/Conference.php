<?php

namespace App;

use Movim\ImageSize;

use Awobaz\Compoships\Database\Eloquent\Model;

class Conference extends Model
{
    public $incrementing = false;
    protected $primaryKey = ['session_id', 'conference'];
    protected $fillable = ['conference', 'name', 'nick', 'autojoin', 'pinned'];
    protected $with = ['contact', 'mujiCalls'];

    public static $xmlnsNotifications = 'urn:xmpp:notification-settings:0';
    public static $xmlnsPinned = 'urn:xmpp:bookmarks-pinning:0';
    public static $notifications = [
        0 => 'never',
        1 => 'on-mention',
        2 => 'always'
    ];

    protected $attributes = [
        'session_id'    => SESSION_ID
    ];

    public static function saveMany(array $conferences)
    {
        return Conference::insert($conferences);
    }

    public function session()
    {
        return $this->hasOne('App\Session');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence', ['jid', 'session_id'], ['conference', 'session_id'])
            ->where('resource', '!=', '')
            ->where('value', '<', 5)
            ->orderBy('mucrole')
            ->orderBy('mucaffiliation', 'desc')
            ->orderBy('value')
            ->orderBy('resource');
    }

    public function mujiCalls()
    {
        return $this->hasMany('App\MujiCall', ['jidfrom', 'session_id'], ['conference', 'session_id'])
            ->where('isfromconference', true);
    }

    public function otherPresences()
    {
        return $this->presences()->where('mucjid', '!=', me()->id);
    }

    public function unreads()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
            ->where('user_id', me()->id)
            ->where('type', 'groupchat')
            ->whereNull('subject')
            ->where('seen', false);
    }

    public function quoted()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
            ->where('user_id', me()->id)
            ->where('type', 'groupchat')
            ->whereNull('subject')
            ->where('quoted', true)
            ->where('seen', false);
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', ['jid', 'session_id'], ['conference', 'session_id'])
            ->where('value', '<', 5)
            ->where('mucjid', me()->id);
    }

    public function members()
    {
        return $this->hasMany('App\Member', 'conference', 'conference')
            ->orderBy('role')
            ->orderBy('affiliation', 'desc');
    }

    public function activeMembers()
    {
        return $this->hasMany('App\Member', 'conference', 'conference')
            ->where('affiliation', '!=', 'outcast')
            ->where('affiliation', '!=', 'none')
            ->orderBy('role')
            ->orderBy('affiliation', 'desc');
    }

    public function pictures()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
            ->where('user_id', me()->id)
            ->where('type', 'groupchat')
            ->where('picture', true)
            ->where('retracted', false)
            ->orderBy('published', 'desc');
    }

    public function links()
    {
        return $this->hasMany('App\Message', 'jidfrom', 'conference')
            ->where('user_id', me()->id)
            ->where('type', 'groupchat')
            ->whereNotNull('urlid')
            ->where('picture', false)
            ->where('retracted', false)
            ->orderBy('published', 'desc');
    }

    public function info()
    {
        return $this->hasOne('App\Info', 'server', 'conference')
            ->where(function ($query) {
                $query->where('node', function ($query) {
                    $query->select('node')
                        ->from('presences')
                        ->where('session_id', me()->session->id)
                        ->whereColumn('jid', 'infos.server')
                        ->where('resource', '')
                        ->take(1);
                })
                    ->orWhere('node', '');
            })
            ->whereCategory('conference')
            ->whereType('text');
    }

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'conference');
    }

    public function set($item)
    {
        $this->conference      = (string)$item->attributes()->id;
        $this->name            = (string)$item->conference->attributes()->name;
        $this->nick            = (string)$item->conference->nick;
        $this->autojoin        = filter_var($item->conference->attributes()->autojoin, FILTER_VALIDATE_BOOLEAN);
        $this->bookmarkversion = (int)substr((string)$item->conference->attributes()->xmlns, -1, 1);

        if ($item->conference->extensions) {
            if (
                $item->conference->extensions->notify
                && $item->conference->extensions->notify->attributes()->xmlns == self::$xmlnsNotifications
            ) {
                if ($item->conference->extensions->notify->never) {
                    $this->notify = 0;
                }

                if ($item->conference->extensions->notify->{'on-mention'}) {
                    $this->notify = 1;
                }

                if ($item->conference->extensions->notify->always) {
                    $this->notify = 2;
                }

                unset($item->conference->extensions->notify);

                // Remove the deprecated extension if present
                if ($item->conference->extensions->notifications) {
                    unset($item->conference->extensions->notifications);
                }
            } else if ( // Deprecated
                $item->conference->extensions->notifications
                && $item->conference->extensions->notifications->attributes()->xmlns == 'xmpp:movim.eu/notifications:0'
            ) {
                $notifications = [
                    'never' => 0,
                    'quoted' => 1,
                    'always' => 2
                ];

                $this->notify = (int)$notifications[(string)$item->conference->extensions->notifications->attributes()->notify];
                unset($item->conference->extensions->notifications);
            }

            if (
                $item->conference->extensions->pinned
                && in_array($item->conference->extensions->pinned->attributes()->xmlns, [self::$xmlnsPinned, 'xmpp:movim.eu/pinned:0'])
            ) {
                $this->pinned = true;
                unset($item->conference->extensions->pinned);
            }

            $this->extensions = $item->conference->extensions->asXML();
        }
    }

    public function getServerAttribute()
    {
        return \explodeJid($this->conference)['server'];
    }

    public function getConnectedAttribute()
    {
        return isset($this->presence);
    }

    public function getNotificationKeyAttribute(): string
    {
        return self::$notifications[$this->notify];
    }

    public function getTitleAttribute()
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        if ($this->info) {
            return $this->info->name;
        }

        if ($this->isGroupChat() && $this->members()->count() > 0) {
            $title = '';
            $i = 0;
            foreach ($this->members()->take(3)->get() as $member) {
                $title .= $member->truename;
                if ($i < 2) $title .= ', ';
                $i++;
            }

            if ($this->members()->count() > 3) $title .= '…';

            return $title;
        }

        return $this->conference;
    }

    public function getSubjectAttribute()
    {
        $subject = me()
            ->messages()
            ->jid($this->conference)
            ->whereNotNull('subject')
            ->whereNull('body')
            ->whereNull('thread')
            ->where('type', 'groupchat')
            ->orderBy('published', 'desc')
            ->first();

        return $subject ? $subject->subject : null;
    }

    public function getPicture(ImageSize $size = ImageSize::M): string
    {
        return $this->contact
            ? $this->contact->getPicture($size)
            : avatarPlaceholder($this->name);
    }

    // https://docs.modernxmpp.org/client/groupchat/#types-of-chat
    public function isGroupChat(): bool
    {
        if ($this->info) {
            return $this->info->mucmembersonly && !$this->info->mucsemianonymous;
        }

        return false;
    }

    public function currentMuji(): ?MujiCall
    {
        return $this->mujiCalls->filter(
            function ($muji) {
                return $muji->joined;
            }
        )->first();
    }

    public function toArray()
    {
        $now = \Carbon\Carbon::now();
        return [
            'session_id' => $this->attributes['session_id'] ?? null,
            'conference' => $this->attributes['conference']  ?? null,
            'name' => $this->attributes['name'] ?? null,
            'nick' => $this->attributes['nick'] ?? null,
            'autojoin' => $this->attributes['autojoin'] ?? null,
            'pinned' => $this->attributes['pinned'] ?? false,
            'created_at' => $this->attributes['created_at'] ?? $now,
            'updated_at' => $this->attributes['updated_at'] ?? $now,
            'extensions' => $this->attributes['extensions'] ?? null,
            'bookmarkversion' => $this->attributes['bookmarkversion'] ?? 0,
            'notify' => $this->attributes['notify'] ?? 1,
        ];
    }
}

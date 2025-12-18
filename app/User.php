<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session;
use App\Contact;
use App\Configuration;
use App\Session as AppSession;
use Illuminate\Database\Capsule\Manager as DB;

class User extends Model
{
    protected $fillable = [
        'id',
        'language',
        'nightmode',
        'chatmain',
        'nsfw',
        'nickname',
        'notificationchat',
        'notificationcall',
        'omemoenabled',
        'accentcolor'
    ];
    public $with = ['session', 'capability'];
    protected $keyType = 'string';
    public $incrementing = false;
    private static $me = null;
    private $unreads = null;

    private $blockListInitialized = false;
    private $userBlocked = [];
    private $globalBlocked = [];

    protected $casts = [
        'posts_since' => 'datetime:Y-m-d H:i:s',
        'notifications_since' => 'datetime:Y-m-d H:i:s',
    ];

    public const ACCENT_COLORS = ['red', 'pink', 'purple', 'dpurple', 'indigo', 'blue', 'cyan', 'teal', 'green', 'lgreen', 'orange', 'dorange'];

    public function save(array $options = [])
    {
        parent::save($options);

        // Reload the user
        self::me(true);
        (new \Movim\Bootstrap)->loadLanguage();
    }

    public function session()
    {
        return $this->hasOne(AppSession::class);
    }

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id');
    }

    public function capability()
    {
        return $this->hasOne(Info::class, 'server', 'id')->where('node', '');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getLastMessage(string $to, bool $muc = false): ?Message
    {
        $m = null;

        if ($muc) {
            // Resolve the current presence
            $presence = $this->session->presences()
                ->where('jid', $to)
                ->where('muc', true)
                ->where('mucjid', $this->id)
                ->first();

            if ($presence) {
                $m = $this->messages()
                    ->where('type', 'groupchat')
                    ->where('jidfrom', $to)
                    ->where('jidto', $this->id)
                    ->where('resource', $presence->resource)
                    ->orderBy('published', 'desc')
                    ->first();
            }
        } else {
            $m = $this->messages()
                ->where('jidto', $to)
                ->orderBy('published', 'desc')
                ->first();
        }

        return $m;
    }

    public function stories()
    {
        return Post::myStories($this);
    }

    public function rosterStories(Roster $roster)
    {
        return $this->stories()->where('server', $roster->jid);
    }

    public function rosterFirstUnseenStory(Roster $roster): ?Post
    {
        return $this->rosterStories($roster)->whereNotIn('id', function ($query) {
            $query->select('post_id')
                ->from('post_user_views')
                ->where('user_id', $this->id);
        })->first();
    }

    public function rosterStoriesAllSeen(Roster $roster): bool
    {
        return !$this->rosterStories($roster)->whereNotIn('id', function ($query) {
            $query->select('post_id')
                ->from('post_user_views')
                ->where('user_id', $this->id);
        })->exists();
    }

    public function MAMEarliests()
    {
        return $this->hasMany(MAMEarliest::class);
    }

    public function openChats()
    {
        return $this->hasMany(OpenChat::class);
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function reported()
    {
        return $this->belongsToMany(Reported::class)->withTimestamps();
    }

    public function postViews()
    {
        return $this->belongsToMany(User::class, 'post_user_views', 'user_id', 'post_id')->withTimestamps();
    }

    public function emojis()
    {
        return $this->belongsToMany(Emoji::class)->withPivot('alias')->withTimestamps();
    }

    public function getUsernameAttribute()
    {
        return $this->contact && $this->contact->nickname
            ? $this->contact->nickname
            : $this->session->username;
    }

    public function getResolvedNicknameAttribute()
    {
        return $this->nickname ?? $this->id;
    }

    public function unreads(?string $jid = null, bool $quoted = false, bool $cached = false): int
    {
        if ($this->unreads !== null && $cached) return $this->unreads;

        $union = DB::table('messages')
            ->where('user_id', $this->id)
            ->where('seen', false)
            ->whereIn('type', ['chat', 'headline', 'invitation']);

        $union = ($jid != null)
            ? $union->where('jidfrom', $jid)
            : $union->where('jidfrom', '!=', $this->id);

        $unreads = $this->messages()
            ->where('seen', false)
            ->where('jidfrom', '!=', $this->id)
            ->where(function ($query) use ($quoted) {
                $query->where('type', 'groupchat')
                    ->whereNull('subject')
                    ->whereIn('jidfrom', function ($query) {
                        $query->select('conference')
                            ->from('conferences')
                            ->where('session_id', function ($query) {
                                $query->select('id')
                                    ->from('sessions')
                                    ->where('user_id', $this->id);
                            });
                    });

                if ($quoted) {
                    $query->where('quoted', true);
                }
            })->unionAll($union);

        $unreads = ($jid != null)
            ? $unreads->where('jidfrom', $jid)
            : $unreads->distinct('jidfrom');

        $unreads = $unreads->count();

        if ($jid == null) {
            $this->unreads = $unreads;
        }

        return $unreads;
    }

    public function encryptedPasswords()
    {
        return $this->hasMany(EncryptedPassword::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'jid', 'id');
    }

    public function affiliations()
    {
        return $this->hasMany(Affiliation::class, 'jid', 'id');
    }

    public static function me($reload = false): ?User
    {
        $session = Session::instance();

        if (
            self::$me != null
            && self::$me->id == $session->get('jid')
            && $reload == false
        ) {
            return self::$me;
        }

        $me = self::find($session->get('jid'));
        self::$me = $me;

        return ($me) ? $me : null;
    }

    public function init()
    {
        $contact = Contact::firstOrNew(['id' => $this->id]);
        $contact->save();
    }

    public function setConfig(array $config)
    {
        if (isset($config['language'])) {
            $this->language = (string)$config['language'];
        }

        if (isset($config['accentcolor']) && in_array($config['accentcolor'], User::ACCENT_COLORS)) {
            $this->accentcolor = (string)$config['accentcolor'];
        }

        if (isset($config['nsfw'])) {
            $this->nsfw = (bool)$config['nsfw'];
        }

        if (isset($config['omemoenabled'])) {
            $this->omemoenabled = (bool)$config['omemoenabled'];
        }

        if (isset($config['chatmain'])) {
            $this->chatmain = (bool)$config['chatmain'];
        }

        if (isset($config['nightmode'])) {
            $this->nightmode = (bool)$config['nightmode'];
        }

        if (isset($config['notificationcall'])) {
            $this->notificationcall = (bool)$config['notificationcall'];
        }

        if (isset($config['notificationchat'])) {
            $this->notificationchat = (bool)$config['notificationchat'];
        }
    }

    public function isRestricted(): bool
    {
        return \App\Configuration::get()->restrictsuggestions;
    }

    public function hasRegister(): bool
    {
        $rootInfo = Info::where('server', explodeJid($this->attributes['id'])['server'])
            ->where('node', '')
            ->first();

        if ($rootInfo) {
            return $rootInfo->hasFeature('jabber:iq:register');
        }

        return false;
    }

    public function hasMAM(): bool
    {
        return ($this->capability && $this->capability->hasFeature('urn:xmpp:mam:2'));
    }

    public function hasBookmarksConvertion(): bool
    {
        return ($this->capability && $this->capability->hasFeature('urn:xmpp:bookmarks:1#compat'));
    }

    public function hasOMEMO(): bool
    {
        return (bool)$this->omemoenabled;
    }

    public function hasPubsub()
    {
        $configuration = Configuration::get();
        return (!$configuration->chatonly
            && $this->capability
            && $this->capability->hasFeature('http://jabber.org/protocol/pubsub#persistent-items')
            && ($this->capability->hasFeature('http://jabber.org/protocol/pubsub#multi-items')
                || ($this->session->serverCapability
                    && $this->session->serverCapability->hasFeature('http://jabber.org/protocol/pubsub#multi-items')
                )
            )
        );
    }

    public function hasUpload(): bool
    {
        return ($this->session && $this->session->getUploadService());
    }

    public function setPublic()
    {
        $this->attributes['public'] = true;
        $this->save();
    }

    public function setPrivate()
    {
        $this->attributes['public'] = false;
        $this->save();
    }

    public function refreshBlocked()
    {
        $this->blockListInitialized = true;
        $this->userBlocked = (array)$this->reported()->get()->pluck('id')->toArray();
        $this->globalBlocked = (array)Reported::where('blocked', true)->get()->pluck('id')->toArray();
    }

    public function hasBlocked(string $jid, bool $localOnly = false): bool
    {
        if ($this->blockListInitialized == false) {
            $this->refreshBlocked();
        }

        if ($localOnly) {
            return in_array($jid, $this->userBlocked);
        }

        return in_array($jid, $this->userBlocked) || in_array($jid, $this->globalBlocked);
    }

    public function isBlocked(Contact $contact): bool
    {
        return $this->hasBlocked($contact->id, true);
    }
}

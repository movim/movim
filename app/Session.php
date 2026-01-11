<?php

namespace App;

use Awobaz\Compoships\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Session extends Model
{
    protected $fillable = ['id'];
    protected $keyType = 'string';
    protected $with = ['serverCapability'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function ownPresences()
    {
        return $this->hasMany(Presence::class, ['jid', 'session_id'], ['user_id', 'id']);
    }

    public function presence()
    {
        return $this->hasOne(Presence::class, ['jid', 'resource', 'session_id'], ['user_id', 'resource', 'id']);
    }

    public function serverCapability()
    {
        return $this->hasOne(Info::class, 'server', 'host')->where('node', '');
    }

    public function contacts()
    {
        return $this->hasMany(Roster::class);
    }

    public function mujiCalls()
    {
        return $this->hasMany(MujiCall::class, 'session_id', 'id');
    }

    public function topContacts()
    {
        return $this->contacts()->join(DB::raw('(
            select jidfrom as id, count(*) as number
            from messages
            where published >= \'' . date('Y-m-d', strtotime('-4 week')) . '\'
            and type = \'chat\'
            and user_id = \'' . $this->user_id . '\'
            group by jidfrom) as top
            '), 'top.id', '=', 'rosters.jid', 'left outer')
            ->orderByRaw('-top.number')
            ->orderBy('jid');
    }

    public function topContactsToChat()
    {
        return $this->topContacts()->join(DB::raw('(
                select min(value) as value, jid as pjid
                from presences
                group by jid) as presences
            '), 'presences.pjid', '=', 'rosters.jid')
            ->where('value', '<', 5)
            ->whereNotIn('rosters.jid', function ($query) {
                $query->select('jid')
                    ->from('open_chats')
                    ->where('user_id', $this->user_id);
            })
            ->where('rosters.jid', '!=', $this->user_id)
            ->with('presence.capability');
    }

    /**
     * @brief Communities subscribed by my contacts that the session is not part of
     */
    public function interestingCommunities(int $limit = 10)
    {
        $where = '(server, node) in (
            select server, node from (
                select count(*) as count, subscriptions.server, subscriptions.node, recents.published
                from subscriptions
                join (select max(published) as published, server, node
                    from posts
                    group by server, node) as recents on recents.server = subscriptions.server and recents.node = subscriptions.node
                where public = true
                and jid in (
                    select jid from rosters where session_id = \'' . $this->id . '\'
                )
                and (subscriptions.server, subscriptions.node) not in (select server, node from subscriptions where jid = \'' . $this->user_id . '\')
                group by subscriptions.server, subscriptions.node, published
                order by published desc, count desc
                limit ' . $limit . '
            ) as sub';

        $configuration = Configuration::get();
        if ($configuration->restrictsuggestions) {
            $host = $this->user->session->host;
            $where .= ' where server like \'%.' . $host . '\'';
        }

        $where .= ')';

        return Info::whereRaw($where);
    }

    public function conferences()
    {
        return $this->hasMany(Conference::class)->orderBy('conference');
    }

    public function init(string $username, string $password, string $host, string $sessionId, string $timezone)
    {
        $this->id          = $sessionId;
        $this->timezone    = $timezone;
        $this->host        = $host;
        $this->username    = $username;
        $this->user_id     = $username . '@' . $host;
        $this->resource    = 'movim' . \generateKey();
        $this->hash        = password_hash(Session::hashSession($this->username, $password, $this->host),  PASSWORD_DEFAULT);
        $this->active      = false;
    }

    public function getUploadService()
    {
        return Info::where(function ($query) {
            $query->where('parent', $this->host)
                ->orWhere('server', $this->host);
        })
            ->whereCategory('store')
            ->whereType('file')
            ->where('features', 'like', '%urn:xmpp:http:upload:0%')
            ->first();
    }

    public function getChatroomsServices()
    {
        return Info::where('parent', $this->host)
            ->whereCategory('conference')
            ->whereDoesntHave('identities', function ($query) {
                $query->where('category', 'gateway');
            })
            ->get();
    }

    public function getMujiService()
    {
        return Info::where('parent', $this->host)
            ->whereCategory('conference')
            ->whereType('text')
            ->whereDoesntHave('identities', function ($query) {
                $query->where('category', 'gateway');
            })
            ->first();
    }

    public function getCommentsService()
    {
        return Info::where('server', 'comments.' . $this->host)
            ->where('parent', $this->host)
            ->whereCategory('pubsub')
            ->whereType('service')
            ->first();
    }

    public static function hashSession(string $username, string $password, string $host): string
    {
        return $username . "\e" . $password . "\e" . $host;
    }
}

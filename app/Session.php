<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session as MemorySession;
use Illuminate\Database\Capsule\Manager as DB;

class Session extends Model
{
    protected $fillable = ['id'];
    protected $keyType = 'string';
    protected $with = ['serverCapability'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence');
    }

    public function ownPresences()
    {
        return $this->hasMany('App\Presence')->where('jid', $this->user_id);
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'user_id')
            ->where('resource', $this->resource)
            ->where('session_id', $this->id);
    }

    public function serverCapability()
    {
        return $this->hasOne('App\Info', 'server', 'host')->where('node', '');
    }

    public function contacts()
    {
        return $this->hasMany('App\Roster');
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
            ->orderByRaw(
                DB::connection()->getDriverName() == 'sqlite'
                    ? '(top.number is null), top.number desc'
                    : '-top.number'
            )
            ->orderBy('jid');
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
            $host = \App\User::me()->session->host;
            $where .= ' where server like \'%.' . $host . '\'';
        }

        $where .= ')';

        return Info::whereRaw($where);
    }

    public function conferences()
    {
        return $this->hasMany('App\Conference')->orderBy('conference');
    }

    public function init(string $username, string $password, string $host, string $timezone)
    {
        $this->id          = SESSION_ID;
        $this->timezone    = $timezone;
        $this->host        = $host;
        $this->username    = $username;
        $this->user_id     = $username . '@' . $host;
        $this->resource    = 'movim' . \generateKey();
        $this->hash        = password_hash(Session::hashSession($this->username, $password, $this->host),  PASSWORD_DEFAULT);
        $this->active      = false;

        // TODO Cleanup
        $session = MemorySession::instance();
        $session->set('password', $password);
    }

    public function loadTimezone()
    {
        define('TIMEZONE', $this->timezone);
        date_default_timezone_set("UTC");
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
            ->get();
    }

    public function getCommentsService()
    {
        return Info::where('server', 'comments.' . $this->host)
            ->where('parent', $this->host)
            ->whereCategory('pubsub')
            ->whereType('service')
            ->first();
    }

    public function loadMemory()
    {
        $session = MemorySession::instance();
        $session->set('jid', $this->user_id);
        $session->set('host', $this->host);
        $session->set('username', $this->username);
    }

    public static function hashSession(string $username, string $password, string $host): string
    {
        return $username . "\e" . $password . "\e" . $host;
    }
}

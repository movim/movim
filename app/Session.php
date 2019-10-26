<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Movim\Session as MemorySession;
use Illuminate\Database\Capsule\Manager as DB;

class Session extends Model
{
    protected $fillable = ['id'];
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function presences()
    {
        return $this->hasMany('App\Presence');
    }

    public function presence()
    {
        return $this->hasOne('App\Presence', 'jid', 'user_id')
                    ->where('resource', $this->resource)
                    ->where('session_id', $this->id);
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
            where published >= \''.date('Y-m-d', strtotime('-4 week')).'\'
            and type = \'chat\'
            and user_id = \''.$this->user_id.'\'
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
    public function interestingCommunities()
    {
        return Info::whereRaw('(server, node) in (
            select server, node from (
                select count(*) as count, server, node
                from subscriptions
                where public = true
                and jid in (
                    select jid from rosters where session_id = \''. $this->id .'\'
                )
                and (server, node) not in (select server, node from subscriptions where jid = \''.$this->user_id.'\')
                group by server, node
                order by count desc
            ) as sub
        )');
    }

    public function conferences()
    {
        return $this->hasMany('App\Conference')->orderBy('conference');
    }

    public function init($username, $password, $host)
    {
        $this->id          = SESSION_ID;
        $this->host        = $host;
        $this->username    = $username;
        $this->user_id     = $username . '@' . $host;
        $this->resource    = 'movim' . \generateKey(6);
        $this->hash        = sha1($this->username . $password . $this->host);
        $this->active      = false;

        // TODO Cleanup
        $s = MemorySession::start();
        $s->set('password', $password);
    }

    public function getUploadService()
    {
        return Info::where('server', 'like', '%' . $this->host)
                   ->whereCategory('store')
                   ->whereType('file')
                   ->where('features', 'like', '%urn:xmpp:http:upload:0%')
                   ->first();
    }

    public function getChatroomsServices()
    {
        return Info::where('server', 'like', '%' . $this->host . '%')
                   ->where('server', 'not like', '%@%')
                   ->whereCategory('conference')
                   ->get();
    }

    public function getCommentsService()
    {
        return Info::where('server', 'comments.' . $this->host)
                   ->whereCategory('pubsub')
                   ->whereType('service')
                   ->first();
    }

    public function loadMemory()
    {
        $s = MemorySession::start();
        $s->set('jid', $this->user_id);
        $s->set('host', $this->host);
        $s->set('username', $this->username);
    }
}

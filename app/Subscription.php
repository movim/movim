<?php

namespace App;

use Movim\Model;

class Subscription extends Model
{
    use \Awobaz\Compoships\Compoships;

    public $incrementing = false;
    protected $primaryKey = ['jid', 'server', 'node'];
    protected $guarded = [];

    public const PUBLIC_NODE = 'urn:xmpp:pubsub:subscription';
    public const SPACE_NODE = '{https://movim.eu}spaces_subscriptions_node';
    public const PRIVATE_NODE = 'urn:xmpp:pubsub:movim-public-subscription';
    public const SUBSCRIPTION_XMLNS = 'urn:xmpp:pubsub:subscription:0';

    public static function saveMany(array $subscriptions)
    {
        return Subscription::insert($subscriptions);
    }

    public function info()
    {
        return $this->hasOne(Info::class, ['server', 'node'], ['server', 'node']);
    }

    public function contact()
    {
        return $this->hasOne(Contact::class, 'id', 'jid');
    }

    public function spaceRooms()
    {
        return $this->hasMany(Conference::class, ['space_server', 'space_node', 'user_id'], ['server', 'node', 'jid'])
            ->orderBy('pinned', 'desc')
            ->orderBy('name', 'asc')
            ->withCount('unreads', 'quoted');
    }

    public function setExtensions(?\SimpleXMLElement $extensions = null)
    {
        if ($extensions) {
            if (
                $extensions->notify
                && $extensions->notify->attributes()->xmlns == Conference::XMLNS_NOTIFICATIONS
            ) {
                if ($extensions->notify->never) {
                    $this->notify = 0;
                }

                if ($extensions->notify->{'on-mention'}) {
                    $this->notify = 1;
                }

                if ($extensions->notify->always) {
                    $this->notify = 2;
                }

                unset($extensions->notify);
            }

            if (
                $extensions->pinned
                && $extensions->pinned->attributes()->xmlns == Conference::XMLNS_PINNED
            ) {
                $this->pinned = true;
                unset($extensions->pinned);
            }

            $this->extensions = $extensions->asXML();
        }
    }

    public function spaceAffiliations()
    {
        return $this->hasMany(Affiliation::class, ['server', 'node'], ['server', 'node']);
    }

    public function getNotifyAttribute(): ?string
    {
        return is_int($this->attributes['notify'])
            ? Conference::NOTIFICATIONS[$this->attributes['notify']]
            : null;
    }

    public function getCounterIdAttribute(): string
    {
        return cleanupId($this->server . $this->node . '-counter');
    }

    public function getUriAttribute(): string
    {
        return 'xmpp:' . $this->server . '?;node=' . $this->node;
    }

    public function spaceUnreads(User $user): int
    {
        return $user->unreads(space: [$this->server, $this->node]);
    }

    public function scopeSpaces($query, ?bool $yes = true)
    {
        return $query->where('space', $yes);
    }

    public function scopeSpace($query, string $server, string $node)
    {
        return $query->where('space', true)
            ->where('server', $server)
            ->where('node', $node);
    }

    public function scopeCommunities($query)
    {
        return $query->where('node', '!=', Post::MICROBLOG_NODE);
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
            'space' => $this->attributes['space'] ?? false,
            'space_in' => $this->attributes['space_in'] ?? false,
            'created_at' => $this->attributes['created_at'] ?? $now,
            'updated_at' => $this->attributes['updated_at'] ?? $now,
            'pinned' => $this->attributes['pinned'] ?? false,
            'extensions' => $this->attributes['extensions'] ?? null,
            'notify' => $this->attributes['notify'] ?? 1,
        ];
    }
}

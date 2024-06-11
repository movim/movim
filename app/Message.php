<?php

namespace App;

use Movim\Model;
use Movim\Image;
use Movim\Session;

use Illuminate\Database\QueryException;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;

class Message extends Model
{
    protected $primaryKey = ['user_id', 'jidfrom', 'id'];
    public $incrementing = false;
    public $mucpm; // Only used in Message Payloads to detect composer/paused PM messages

    protected $guarded = [];

    protected $with = ['reactions', 'parent.from', 'resolvedUrl', 'replace', 'file'];

    protected $attributes = [
        'type'    => 'chat'
    ];

    protected $casts = [
        'quoted'   => 'boolean',
        'markable' => 'boolean'
    ];

    private ?Collection $messageFiles = null;

    public function save(array $options = [])
    {
        try {
            $saved = parent::save($options);

            if ($saved && $this->messageFiles != null && $this->messageFiles->isNotEmpty()) {
                $mid = $this->mid;

                if ($mid == null) {
                    $mid = DB::getPdo()->lastInsertId();
                    MessageFile::where('message_mid', $mid)->delete();
                }

                $this->messageFiles->each(function ($file) use ($mid) {
                    $file->message_mid = $mid;
                    $file->save();
                });
            }
        } catch (\Exception $e) {
            \Utils::error($e->getMessage());
        }
    }

    public function parent()
    {
        return $this->belongsTo('App\Message', 'parentmid', 'mid');
    }

    public function replace()
    {
        return $this->belongsTo('App\Message', 'replaceid', 'originid')->without('replace');
    }

    public function resolvedUrl()
    {
        return $this->belongsTo('App\Url', 'urlid', 'id');
    }

    public function from()
    {
        return $this->belongsTo('App\Contact', 'jidfrom', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopeJid($query, string $jid)
    {
        $jidFromToMessages = DB::table('messages')
            ->where('user_id', \App\User::me()->id)
            ->where('jidfrom', $jid)
            ->unionAll(
                DB::table('messages')
                    ->where('user_id', \App\User::me()->id)
                    ->where('jidto', $jid)
            );

        return $query->select('*')->from(
            $jidFromToMessages,
            'messages'
        )->where('user_id', \App\User::me()->id);
    }

    public function reactions()
    {
        return $this->hasMany('App\Reaction', 'message_mid', 'mid');
    }

    public function file()
    {
        return $this->hasOne('App\MessageFile', 'message_mid', 'mid');
    }

    public function files()
    {
        return $this->hasMany('App\MessageFile', 'message_mid', 'mid');
    }

    public function getOmemoheaderAttribute()
    {
        return $this->attributes['omemoheader'] !== null ? unserialize($this->attributes['omemoheader']) : null;
    }

    public function getJidfromAttribute()
    {
        return \unechap($this->attributes['jidfrom']);
    }

    public static function findByStanza(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null): Message
    {
        $jidfrom = baseJid((string)$stanza->attributes()->from);

        if (
            $stanza->attributes()->xmlns
            && $stanza->attributes()->xmlns == 'urn:xmpp:mam:2'
        ) {
            return self::firstOrNew([
                'user_id' => \App\User::me()->id,
                'stanzaid' => (string)$stanza->attributes()->id,
                'jidfrom' => baseJid((string)$stanza->forwarded->message->attributes()->from)
            ]);
        } elseif (
            $stanza->{'stanza-id'} && $stanza->{'stanza-id'}->attributes()->id
            && ($stanza->{'stanza-id'}->attributes()->by == $jidfrom
                || $stanza->{'stanza-id'}->attributes()->by == \App\User::me()->id
            )
        ) {
            return self::firstOrNew([
                'user_id' => \App\User::me()->id,
                'stanzaid' => (string)$stanza->{'stanza-id'}->attributes()->id,
                'jidfrom' => $jidfrom
            ]);
        } else {
            $message = new Message;
            $message->user_id = \App\User::me()->id;
            $message->id = 'm_' . generateUUID();
            $message->jidfrom = $jidfrom;
            return $message;
        }
    }

    public static function getLast(string $to, bool $muc = false): ?Message
    {
        $m = null;

        if ($muc) {
            // Resolve the current presence
            $presence = \App\User::me()->session->presences()
                ->where('jid', $to)
                ->where('muc', true)
                ->where('mucjid', \App\User::me()->id)
                ->first();

            if ($presence) {
                $m = \App\User::me()->messages()
                    ->where('type', 'groupchat')
                    ->where('jidfrom', $to)
                    ->where('jidto', \App\User::me()->id)
                    ->where('resource', $presence->resource)
                    ->orderBy('published', 'desc')
                    ->first();
            }
        } else {
            $m = \App\User::me()->messages()
                ->where('jidto', $to)
                ->orderBy('published', 'desc')
                ->first();
        }

        return $m;
    }

    public function isLast(): bool
    {
        $last = Message::getLast($this->isMuc() ? $this->jidfrom : $this->jidto, $this->isMuc());

        return ($last && $this->mid == $last->mid);
    }

    public static function eventMessageFactory(string $type, string $from, string $thread): Message
    {
        $userid = \App\User::me()->id;
        $message = new \App\Message;
        $message->user_id = $userid;
        $message->id = 'm_' . generateUUID();
        $message->jidto = $userid;
        $message->jidfrom = $from;
        $message->published = gmdate('Y-m-d H:i:s');
        $message->thread = $thread;
        $message->type = $type;

        return $message;
    }

    public function clearUnreads()
    {
        if ($this->jidfrom == $this->user_id) {
            $this->user->messages()
                ->where('jidfrom', $this->jidto)
                ->where('seen', false)
                ->update(['seen' => true]);
        }
    }

    public function set($stanza, $parent = false)
    {
        $this->messageFiles = collect();

        // We reset the URL resolution to refresh it once the message is displayed
        $this->resolved = false;

        $jidTo = explodeJid((string)$stanza->attributes()->to);
        $jidFrom = explodeJid((string)$stanza->attributes()->from);

        $this->user_id    = \App\User::me()->id;

        $this->id = 'm_' . generateUUID();

        if ($stanza->attributes()->id) {
            $this->messageid  = (string)$stanza->attributes()->id;
        }

        if (!$this->jidto) {
            $this->jidto      = $jidTo['jid'];
        }

        if (!$this->jidfrom) {
            $this->jidfrom    = $jidFrom['jid'];
        }

        // If the message is from me
        if ($this->jidfrom == $this->user_id) {
            $this->seen = true;
        }

        if (isset($jidFrom['resource'])) {
            $this->resource = $jidFrom['resource'];
        }

        if ($stanza->delay) {
            $this->published = gmdate('Y-m-d H:i:s', strtotime($stanza->delay->attributes()->stamp));
        } elseif ($parent && $parent->delay) {
            $this->published = gmdate('Y-m-d H:i:s', strtotime($parent->delay->attributes()->stamp));
        } elseif (!isset($stanza->replace) || $this->published === null) {
            $this->published = gmdate('Y-m-d H:i:s');
        }

        $this->type = 'chat';
        if ($stanza->attributes()->type) {
            $this->type = (string)$stanza->attributes()->type;
        }

        // https://xmpp.org/extensions/xep-0359.html#stanza-id
        if (
            $stanza->{'origin-id'}
            && (string)$stanza->{'origin-id'}->attributes()->xmlns == 'urn:xmpp:sid:0'
        ) {
            $this->originid = (string)$stanza->{'origin-id'}->attributes()->id;
        }

        // https://xmpp.org/extensions/xep-0359.html#origin-id for groupchat only
        if (
            $this->isMuc()
            && $stanza->{'stanza-id'}
            && $stanza->{'stanza-id'}->attributes()->id
            && (string)$stanza->{'stanza-id'}->attributes()->xmlns == 'urn:xmpp:sid:0'
            && ($stanza->{'stanza-id'}->attributes()->by == $this->jidfrom
                || $stanza->{'stanza-id'}->attributes()->by == \App\User::me()->id
            )
        ) {
            if ($this->isMuc()) {
                $session = Session::start();

                // Cache the state in Session for performances purpose
                $sessionKey = $this->jidfrom . '_stanza_id';
                $conferenceStanzaIdEnabled = $session->get($sessionKey, null);

                if ($conferenceStanzaIdEnabled == null) {
                    $conference = $this->user->session->conferences()
                        ->where('conference', $this->jidfrom)
                        ->first();

                    $session->set($sessionKey, $conference && $conference->info && $conference->info->hasStanzaId());
                }

                if ($session->get($sessionKey, false)) {
                    $this->stanzaid = (string)$stanza->{'stanza-id'}->attributes()->id;
                }
            } else {
                $this->stanzaid = (string)$stanza->{'stanza-id'}->attributes()->id;
            }
        }

        // If it's a MUC message, we assume that the server already handled it
        if ($this->isMuc()) {
            $this->delivered = gmdate('Y-m-d H:i:s');
        }

        if (
            $this->type !== 'groupchat'
            && $stanza->x
            && (string)$stanza->x->attributes()->xmlns == 'http://jabber.org/protocol/muc#user'
        ) {
            $this->mucpm = true;
            if ($parent && (string)$parent->attributes()->xmlns == 'urn:xmpp:forward:0') {
                $this->jidto = (string)$stanza->attributes()->to;
            } elseif (isset($jidFrom['resource'])) {
                $this->jidfrom = $jidFrom['jid'] . '/' . $jidFrom['resource'];
            }
        }


        # XEP-0444: Message Reactions
        if (
            isset($stanza->reactions)
            && $stanza->reactions->attributes()->xmlns == 'urn:xmpp:reactions:0'
        ) {

            $parentMessage = $this->resolveParentMessage($this->jidfrom, (string)$stanza->reactions->attributes()->id);

            if ($parentMessage) {
                $resource = $this->isMuc()
                    ? $this->resource
                    : $this->jidfrom;

                $parentMessage
                    ->reactions()
                    ->where('jidfrom', $resource)
                    ->delete();

                $emojis = [];
                $now = \Carbon\Carbon::now();
                $emoji = \Movim\Emoji::getInstance();

                foreach ($stanza->reactions->reaction as $children) {
                    $emoji->replace((string)$children);
                    if ($emoji->isSingleEmoji()) {
                        $reaction = new Reaction;
                        $reaction->message_mid = $parentMessage->mid;
                        $reaction->emoji = (string)$children;
                        $reaction->jidfrom = $resource;
                        $reaction->created_at = $now;
                        $reaction->updated_at = $now;

                        \array_push($emojis, $reaction->toArray());
                    }
                }

                try {
                    Reaction::insert($emojis);
                } catch (QueryException $e) {
                    // Duplicate ?
                    \Utils::error($e->getMessage());
                }

                return $parentMessage;
            }

            return null;
        } elseif ($stanza->body || $stanza->subject) {
            if ($stanza->body) {
                $this->body = (string)$stanza->body;
            }

            if ($stanza->subject) {
                $this->subject = (string)$stanza->subject;
            }

            if ($stanza->thread) {
                $this->thread = (string)$stanza->thread;
            }

            // XEP-0333: Chat Markers
            $this->markable = (bool)($stanza->markable && $stanza->markable->attributes()->xmlns == 'urn:xmpp:chat-markers:0');

            // Reply can be handled by XEP-0461: Message Replies or by the threadid Jabber mechanism
            if ($stanza->reply && $stanza->reply->attributes()->xmlns == 'urn:xmpp:reply:0') {
                $parentMessage = $this->resolveParentMessage($this->jidfrom, (string)$stanza->reply->attributes()->id);

                if (
                    $parentMessage && $parentMessage->mid != $this->mid
                    && $parentMessage->originid != $this->originid
                ) {
                    $this->parentmid = $parentMessage->mid;
                }

                if (
                    $stanza->fallback && $stanza->fallback->attributes()->xmlns == 'urn:xmpp:fallback:0'
                    && $stanza->fallback->attributes()->for == 'urn:xmpp:reply:0'
                ) {
                    $this->body = mb_substr(
                        htmlspecialchars_decode($this->body, ENT_XML1),
                        (int)$stanza->fallback->body->attributes()->end
                    );
                }
            }

            if ($this->isMuc()) {
                $presence = $this->user->session->presences()
                    ->where('jid', $this->jidfrom)
                    ->where('mucjid', $this->user->id)
                    ->first();

                if (
                    $presence
                    && $this->body != null
                    && strpos($this->body, $presence->resource) !== false
                    && $this->resource != $presence->resource
                ) {
                    $this->quoted = true;
                }
            }

            if ($stanza->html) {
                $results = [];

                $xml = \simplexml_load_string((string)$stanza->html);
                if (!$xml) {
                    $xml = \simplexml_load_string((string)$stanza->html->body);
                    if ($xml) {
                        $results = $xml->xpath('//img/@src');
                    }
                } else {
                    $xml->registerXPathNamespace('xhtml', 'http://www.w3.org/1999/xhtml');
                    $results = $xml->xpath('//xhtml:img/@src');
                }

                if (!empty($results)) {
                    if (substr((string)$results[0], 0, 10) == 'data:image') {
                        $str = explode('base64,', $results[0]);
                        if (isset($str[1])) {
                            $p = new Image;
                            $p->fromBase(urldecode($str[1]));
                            $key = sha1(urldecode($str[1]));
                            $p->setKey($key);
                            $p->save(false, false, 'png');

                            $this->sticker = $key;
                        }
                    } else {
                        $this->sticker = getCid((string)$results[0]);
                    }
                }
            }

            // XEP-0385: Stateless Inline Media Sharing (SIMS)
            if (
                $stanza->reference
                && (string)$stanza->reference->attributes()->xmlns == 'urn:xmpp:reference:0'
            ) {
                $messageFile = new MessageFile;

                if (
                    $stanza->reference->{'media-sharing'}
                    && (string)$stanza->reference->{'media-sharing'}->attributes()->xmlns == 'urn:xmpp:sims:1'
                ) {
                    $file = $stanza->reference->{'media-sharing'}->file;

                    if (isset($file)) {
                        if (preg_match('/\w+\/[-+.\w]+/', $file->{'media-type'}) == 1) {
                            $messageFile->type = (string)$file->{'media-type'};
                        }
                        $messageFile->size = (int)$file->size;
                        $messageFile->name = (string)$file->name;
                    }

                    if ($stanza->reference->{'media-sharing'}->sources) {
                        $source = $stanza->reference->{'media-sharing'}->sources->reference;

                        if (!filter_var((string)$source->attributes()->uri, FILTER_VALIDATE_URL) === false) {
                            $messageFile->url = (string)$source->attributes()->uri;
                        }
                    }

                    if (
                        $stanza->reference->{'media-sharing'}->file->thumbnail
                        && (string)$stanza->reference->{'media-sharing'}->file->thumbnail->attributes()->xmlns == 'urn:xmpp:thumbs:1'
                    ) {
                        $thumbnailAttributes = $stanza->reference->{'media-sharing'}->file->thumbnail->attributes();

                        if (!filter_var((string)$thumbnailAttributes->uri, FILTER_VALIDATE_URL) === false) {
                            $messageFile->thumbnail_width = (int)$thumbnailAttributes->width;
                            $messageFile->thumbnail_height = (int)$thumbnailAttributes->height;
                            $messageFile->thumbnail_type = (string)$thumbnailAttributes->{'media-type'};
                            $messageFile->thumbnail_url = (string)$thumbnailAttributes->uri;
                        }

                        if (substr((string)$thumbnailAttributes->uri, 0, 21) == 'data:image/thumbhash,') {
                            $messageFile->thumbnail_width = (int)$thumbnailAttributes->width;
                            $messageFile->thumbnail_height = (int)$thumbnailAttributes->height;
                            $messageFile->thumbnail_type = (string)$thumbnailAttributes->{'media-type'};
                            $messageFile->thumbnail_url = substr((string)$thumbnailAttributes->uri, 21);
                        }
                    }

                    if (
                        $messageFile->url
                        && $messageFile->type
                        && $messageFile->size
                        && $messageFile->name
                    ) {
                        if (empty($messageFile->name)) {
                            $messageFile->name =
                                pathinfo(parse_url($messageFile->uri, PHP_URL_PATH), PATHINFO_BASENAME)
                                . ' (' . parse_url($messageFile->uri, PHP_URL_HOST) . ')';
                        }

                        $this->picture = $messageFile->isPicture;
                        $this->messageFiles->push($messageFile);
                    }
                } /*elseif (
                    \in_array($stanza->reference->attributes()->type, ['mention', 'data'])
                    && $stanza->reference->attributes()->uri
                ) {

                    $uri = parse_url($stanza->reference->attributes()->uri);

                    if ($uri['scheme'] === 'xmpp') {
                        $begin = '<a href="' . Route::urlize('share', $stanza->reference->attributes()->uri) . '">';

                        if ($stanza->reference->attributes()->begin && $stanza->reference->attributes()->end) {
                            $this->html = substr_replace(
                                $this->body,
                                $begin,
                                (int)$stanza->reference->attributes()->begin,
                                0
                            );
                            $this->html = substr_replace(
                                $this->html,
                                '</a>',
                                (int)$stanza->reference->attributes()->end + strlen($begin),
                                0
                            );
                        } else {
                            $this->html = $begin . $this->body . '</a>';
                        }

                        $this->file = [
                            'type' => 'xmpp',
                            'uri' => (string)$stanza->reference->attributes()->uri,
                        ];
                    }
                }*/
            }

            if (
                $stanza->encryption
                && (string)$stanza->encryption->attributes()->xmlns == 'urn:xmpp:eme:0'
            ) {
                $this->encrypted = true;
            }

            if (
                $stanza->replace
                && (string)$stanza->replace->attributes()->xmlns == 'urn:xmpp:message-correct:0'
            ) {
                // Here the replaceid could be a bad one, we will handle it later
                $this->replaceid = (string)$stanza->replace->attributes()->id;
            }

            if (isset($stanza->x->invite)) {
                $this->type = 'invitation';
                $this->subject = $this->jidfrom;
                $this->jidfrom = baseJid((string)$stanza->x->invite->attributes()->from);
            }
        } elseif (
            isset($stanza->x)
            && $stanza->x->attributes()->xmlns == 'jabber:x:conference'
        ) {
            $this->type = 'invitation';
            $this->body = (string)$stanza->x->attributes()->reason;
            $this->subject = (string)$stanza->x->attributes()->jid;
        }

        # XEP-0384 OMEMO Encryption
        if (
            isset($stanza->encrypted)
            && $stanza->encrypted->attributes()->xmlns == 'eu.siacs.conversations.axolotl'
        ) {
            $omemoHeader = new MessageOmemoHeader;
            $omemoHeader->set($stanza);
            $this->attributes['omemoheader'] = (string)$omemoHeader;
            $this->attributes['bundleid'] = (int)$omemoHeader->sid;
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return (empty($this->body)
            && empty($this->sticker)
            && !$this->file
        );
    }

    public function isMuc(): bool
    {
        return ($this->type == 'groupchat');
    }

    public function isSubject(): bool
    {
        return !empty($this->subject);
    }

    public function isMine(): bool
    {
        return ($this->user_id == $this->jidfrom);
    }

    public function isClassic(): bool
    {
        return in_array($this->type,  ['chat', 'groupchat']);
    }

    public function retract()
    {
        $this->retracted = true;
        $this->oldid = null;
        $this->body = $this->html = 'retracted';
    }

    public function addUrls()
    {
        if (is_string($this->body)) {
            $old = $this->body;
            $this->body = addUrls($this->body);

            // TODO fix addUrls, see https://github.com/movim/movim/issues/877
            if (strlen($this->body) < strlen($old)) {
                $this->body = $old;
            }
        }
    }

    public function resolveColor()
    {
        $this->color = stringToColor(
            $this->resource . $this->type
        );

        return $this->color;
    }

    public function valid()
    {
        return
            strlen($this->attributes['jidto']) < 256
            && strlen($this->attributes['jidfrom']) < 256
            && (!isset($this->attributes['resource']) || strlen($this->attributes['resource']) < 256)
            && (!isset($this->attributes['thread']) || strlen($this->attributes['thread']) < 128)
            && (!isset($this->attributes['replaceid']) || strlen($this->attributes['replaceid']) < 64)
            && (!isset($this->attributes['originid']) || strlen($this->attributes['originid']) < 255)
            && (!isset($this->attributes['id']) || strlen($this->attributes['id']) < 64)
            && (!isset($this->attributes['oldid']) || strlen($this->attributes['oldid']) < 64);
    }

    // toArray is already used
    public function toRawArray()
    {
        $array = [
            'user_id' => $this->attributes['user_id'] ?? null,
            'id' => $this->attributes['id'] ?? null,
            'oldid' => $this->attributes['oldid'] ?? null,
            'jidto' => $this->attributes['jidto'] ?? null,
            'jidfrom' => $this->attributes['jidfrom'] ?? null,
            'resource' => $this->attributes['resource'] ?? null,
            'type' => $this->attributes['type'] ?? null,
            'subject' => $this->attributes['subject'] ?? null,
            'thread' => $this->attributes['thread'] ?? null,
            'body' => $this->attributes['body'] ?? null,
            'html' => $this->attributes['html'] ?? null,
            'published' => $this->attributes['published'] ?? null,
            'delivered' => $this->attributes['deliver'] ?? null,
            'displayed' => $this->attributes['displayed'] ?? null,
            'quoted' => $this->attributes['quoted'] ?? false,
            'markable' => $this->attributes['markable'] ?? false,
            'sticker' => $this->attributes['sticker'] ?? null,
            'created_at' => $this->attributes['created_at'] ?? null,
            'updated_at' => $this->attributes['updated_at'] ?? null,
            'replaceid' => $this->attributes['replaceid'] ?? null,
            'seen' => $this->attributes['seen'] ?? false,
            'encrypted' => $this->attributes['encrypted'] ?? false,
            'originid' => $this->attributes['originid'] ?? null,
            'retracted' => $this->attributes['retracted'] ?? false,
            'resolved' => $this->attributes['resolved'] ?? false,
            'picture' => $this->attributes['picture'] ?? false,
            'parentmid' => $this->attributes['parentmid'] ?? null,
        ];

        // Generate a proper mid
        if (
            empty($this->attributes['mid'])
            || ($this->attributes['mid'] && $this->attributes['mid'] == 1)
        ) {
            $array['mid'] = $this->getNextStatementId();
        } else {
            $array['mid'] = $this->attributes['mid'];
        }

        return $array;
    }

    public function getNextStatementId()
    {
        $next_id = DB::select("select nextval('messages_mid_seq'::regclass)");
        return intval($next_id['0']->nextval);
    }

    // https://xmpp.org/extensions/xep-0444.html#business-id
    // https://xmpp.org/extensions/xep-0461.html#business-id
    private function resolveParentMessage(string $from, string $id): ?Message
    {
        $parentMessage = null;

        if ($this->isMuc()) {
            $parentMessage = $this->user->messages()->jid($from)
                ->where('stanzaid', $id)
                ->first();
        } else {
            $parentMessage = $this->user->messages()->jid($from)
                ->where('messageid', $id)
                ->first();

            // Rare case, origin-id
            if (!$parentMessage) {
                $parentMessage = $this->user->messages()->jid($from)
                    ->where('originid', $id)
                    ->first();
            }
        }

        return $parentMessage;
    }
}

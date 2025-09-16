<?php

namespace App;

use DOMDocument;
use DOMXPath;
use Movim\Model;
use Movim\Image;
use Movim\Session;

use Illuminate\Database\QueryException;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use Movim\XMPPUri;
use Moxl\Xec\Action\BOB\Request;
use Moxl\Xec\Action\Pubsub\GetItem;

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

    public static $inlinePlaceholder = 'inline-img:';

    public const MESSAGE_TYPE = [
        'chat',
        'headline',
        'invitation',
        'jingle_end',
        'jingle_finish',
        'jingle_incoming',
        'jingle_outgoing',
        'jingle_reject',
        'jingle_retract',
    ];
    public const MESSAGE_TYPE_MUC = [
        'groupchat',
        'muc_admin',
        'muc_member',
        'muc_outcast',
        'muc_owner',
        'muji_propose',
        'muji_retract',
    ];

    public static function boot()
    {
        parent::boot();

        static::saved(function (Message $message) {
            if ($message->messageFiles != null && $message->messageFiles->isNotEmpty()) {
                $mid = Message::where('id', $message->id)
                    ->where('user_id', me()->id)
                    ->where('jidfrom', $message->jidfrom)
                    ->first()
                    ->mid;

                MessageFile::where('message_mid', $mid)->delete();

                $message->messageFiles->each(function ($file) use ($mid) {
                    $file->message_mid = $mid;
                    $file->save();
                });
            }

            $message->resolvePost();
        });
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

    public function post()
    {
        return $this->belongsTo('App\Post', 'postid', 'id');
    }

    public function scopeJid($query, string $jid)
    {
        $jidFromToMessages = DB::table('messages')
            ->where('user_id', me()->id)
            ->where('jidfrom', $jid)
            ->unionAll(
                DB::table('messages')
                    ->where('user_id', me()->id)
                    ->where('jidto', $jid)
            );

        return $query->select('*')->from(
            $jidFromToMessages,
            'messages'
        )->where('user_id', me()->id);
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

    public function getStickerImageAttribute(): ?Image
    {
        $image = new Image;
        $image->setKey($this->sticker_cid_hash);

        if ($image->load()) {
            return $image;
        }

        return null;
    }

    public function getInlinesAttribute(): ?array
    {
        return array_key_exists('inlines', $this->attributes) && $this->attributes['inlines'] !== null
            ? unserialize($this->attributes['inlines'])
            : null;
    }

    public function getOmemoheaderAttribute(): ?array
    {
        return array_key_exists('omemoheader', $this->attributes) && $this->attributes['omemoheader'] !== null
            ? unserialize($this->attributes['omemoheader'])
            : null;
    }

    public function getJidfromAttribute()
    {
        return \unechap($this->attributes['jidfrom']);
    }

    public function getJidAttribute()
    {
        return $this->attributes['jidfrom'] == me()->id
            ? \unechap($this->attributes['jidto'])
            : \unechap($this->attributes['jidfrom']);
    }

    public static function findByStanza(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null): Message
    {
        $jidfrom = baseJid((string)$stanza->attributes()->from);

        if (
            $stanza->attributes()->xmlns
            && $stanza->attributes()->xmlns == 'urn:xmpp:mam:2'
        ) {
            return self::firstOrNew([
                'user_id' => me()->id,
                'stanzaid' => (string)$stanza->attributes()->id,
                'jidfrom' => baseJid((string)$stanza->forwarded->message->attributes()->from)
            ]);
        } elseif (
            $stanza->{'stanza-id'} && $stanza->{'stanza-id'}->attributes()->id
            && ($stanza->{'stanza-id'}->attributes()->by == $jidfrom
                || $stanza->{'stanza-id'}->attributes()->by == me()->id
            )
        ) {
            return self::firstOrNew([
                'user_id' => me()->id,
                'stanzaid' => (string)$stanza->{'stanza-id'}->attributes()->id,
                'jidfrom' => $jidfrom
            ]);
        } else {
            $message = new Message;
            $message->user_id = me()->id;
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
            $presence = me()->session->presences()
                ->where('jid', $to)
                ->where('muc', true)
                ->where('mucjid', me()->id)
                ->first();

            if ($presence) {
                $m = me()->messages()
                    ->where('type', 'groupchat')
                    ->where('jidfrom', $to)
                    ->where('jidto', me()->id)
                    ->where('resource', $presence->resource)
                    ->orderBy('published', 'desc')
                    ->first();
            }
        } else {
            $m = me()->messages()
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
        $userid = me()->id;
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

        $this->user_id    = me()->id;

        if (!$this->id) {
            $this->id = 'm_' . generateUUID();
        }

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
                || $stanza->{'stanza-id'}->attributes()->by == me()->id
            )
        ) {
            if ($this->isMuc()) {
                $session = Session::instance();

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
                    logError($e);
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
                $presence = $this->user->session?->presences()
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

            if (
                $stanza->html
                && (string)$stanza->html->attributes()->xmlns = 'http://jabber.org/protocol/xhtml-im'
            ) {
                $head = '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';
                $dom = new DOMDocument('1.0', 'UTF-8');
                $dom->loadHTML($head .  (string)$stanza->html->body);
                $xpath = new DOMXPath($dom);
                $imgs = $xpath->query("//img[starts-with(@src,'cid:')]");

                if ($imgs && $imgs->count() >= 1) {
                    $texts = $xpath->query('//p/text()');

                    // Message with inline images
                    if (($imgs->count() > 1) || ($texts && $texts->count() > 0)) {
                        $inlines = [];

                        foreach ($imgs as $img) {
                            $key = generateKey(12);
                            $cid = getCid($img->getAttribute('src'));

                            $inlines[$key] = [
                                'hash' => $cid['hash'],
                                'algorythm' => $cid['algorythm'],
                                'alt' => $img->getAttribute('alt'),
                            ];
                            $img->replaceWith(self::$inlinePlaceholder . $key);
                        }

                        $this->attributes['inlines'] = serialize($inlines);
                        $this->body = (string)$dom->textContent;
                    }

                    // One sticker only
                    elseif ($imgs->count() == 1) {
                        $cid = getCid($imgs->item(0)->getAttribute('src'));

                        if ($cid) {
                            $this->sticker_cid_hash = $cid['hash'];
                            $this->sticker_cid_algorythm = $cid['algorythm'];
                        }
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

                    // We extract the inline disposition from SFS if present
                    if (
                        $stanza->{'file-sharing'}
                        && $stanza->{'file-sharing'}->attributes()->xmlns == 'urn:xmpp:sfs:0'
                        && $stanza->{'file-sharing'}->attributes()->disposition
                        && $stanza->{'file-sharing'}->sources
                        && $stanza->{'file-sharing'}->sources->{'url-data'}
                        && $stanza->{'file-sharing'}->sources->{'url-data'}->attributes()->xmlns == 'http://jabber.org/protocol/url-data'
                        && $stanza->{'file-sharing'}->sources->{'url-data'}->attributes()->target == $messageFile->url
                    ) {
                        $messageFile->disposition = $stanza->{'file-sharing'}->attributes()->disposition;
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

                        if (substr((string)$thumbnailAttributes->uri, 0, 28) == 'data:image/thumbhash;base64,') {
                            $messageFile->thumbnail_width = (int)$thumbnailAttributes->width;
                            $messageFile->thumbnail_height = (int)$thumbnailAttributes->height;
                            $messageFile->thumbnail_type = (string)$thumbnailAttributes->{'media-type'};
                            $messageFile->thumbnail_url = substr((string)$thumbnailAttributes->uri, 28);
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
                } else {
                    $this->posturi = (string)$stanza->reference->attributes()->uri;
                }
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

    /**
     * @desc Resolve the Post from its URI, require a saved message
     */
    public function resolvePost()
    {
        if (!$this->posturi || !empty($this->postid)) return;

        $xmppUri = new XMPPUri($this->posturi);

        if ($post = $xmppUri->getPost()) {
            $this->postid = $post->id;
        } elseif ($xmppUri->getServer() && $xmppUri->getNode() && $xmppUri->getNodeItemId()) {
            $getItem = new GetItem;
            $getItem->setTo($xmppUri->getServer())
                ->setNode($xmppUri->getNode())
                ->setId($xmppUri->getNodeItemId())
                ->setMessagemid($this->mid)
                ->request();
        }
    }

    /**
     * @desc Prepare and return the body with inline images, and request them if missing
     */
    public function getInlinedBodyAttribute(?bool $alt = false, bool $triggerRequest = false): ?string
    {
        if (!array_key_exists('body', $this->attributes)) return null;

        $body = $this->attributes['body'];

        if (is_array($this->getInlinesAttribute())) {
            foreach ($this->getInlinesAttribute() as $key => $inline) {
                if ($alt == true) {
                    $body = str_replace(
                        Message::$inlinePlaceholder . $key,
                        $inline['alt'],
                        $body
                    );

                    continue;
                }

                $url = Image::getOrCreate($inline['hash']);

                if ($url) {
                    $dom = new \DOMDocument('1.0', 'UTF-8');
                    $img = $dom->createElement('img');
                    $img->setAttribute('class', 'inline');
                    $img->setAttribute('src', $url);
                    $img->setAttribute('alt', $inline['alt']);
                    $img->setAttribute('title', $inline['alt']);
                    $dom->append($img);

                    $body = str_replace(
                        Message::$inlinePlaceholder . $key,
                        $dom->saveHTML($dom->documentElement),
                        $body
                    );
                } else {
                    $body = str_replace(
                        Message::$inlinePlaceholder . $key,
                        $inline['alt'],
                        $body
                    );

                    if ($triggerRequest) {
                        $r = new Request;
                        $r->setTo($this->attributes['jidfrom'])
                            ->setResource($this->attributes['resource'])
                            ->setHash($inline['hash'])
                            ->setAlgorythm($inline['algorythm'])
                            ->request();
                    }
                }
            }
        }

        return $body;
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
        if ($this->isMuc()) {
            return $this->user->session->presences()
                ->where('jid', $this->jidfrom)
                ->where('resource', $this->resource)
                ->where('mucjid', $this->user_id)
                ->where('muc', true)
                ->count() > 0;
        }

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
            $this->body = addUrls($this->body);
        }
    }

    public function resolveColor(): string
    {
        $this->color = stringToColor($this->resource);

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
            'inline' => $this->attributes['inlines'] ?? null,
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

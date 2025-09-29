<?php

namespace App;

use Respect\Validation\Validator;

use Awobaz\Compoships\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use Movim\Widget\Wrapper;
use Moxl\Xec\Payload\Packet;
use React\Promise\Promise;
use SimpleXMLElement;

class Post extends Model
{
    protected $primaryKey = 'id';

    protected $guarded = [];
    public $with = [
        'attachments',
        'likes',
        'comments',
        'contact',
        'links',
        'userAffiliation'
    ];
    public $withCount = ['userViews'];

    private $titleLimit = 700;
    private $changed = false; // Detect if the set post was different from the cache

    public array $attachments = [];
    public array $resolvableAttachments = [];
    public $tags = [];

    public const MICROBLOG_NODE = 'urn:xmpp:microblog:0';
    public const COMMENTS_NODE = 'urn:xmpp:microblog:0:comments';
    public const STORIES_NODE = 'urn:xmpp:pubsub-social-feed:stories:0';

    public function contact()
    {
        return $this->hasOne('App\Contact', 'id', 'aid');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Tag')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany('App\Post', 'parent_id', 'id')
            ->orderBy('published')
            ->where('like', false);
    }

    public function parent()
    {
        return $this->hasOne('App\Post', 'id', 'parent_id');
    }

    public function info()
    {
        return $this->hasOne('App\Info', ['server', 'node'], ['server', 'node']);
    }

    public function userAffiliation()
    {
        return $this->hasOne('App\Affiliation', ['server', 'node'], ['server', 'node'])
            ->where('jid', me()->id);
    }

    public function userViews()
    {
        return $this->belongsToMany(User::class, 'post_user_views', 'post_id', 'user_id')->withTimestamps();
    }

    public function myViews()
    {
        return $this->userViews()->where('user_id', me()->id);
    }

    public function likes()
    {
        return $this->hasMany('App\Post', 'parent_id', 'id')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('min(id) as id'))
                    ->from('posts')
                    ->where('like', true)
                    ->whereNotNull('aid')
                    ->groupByRaw('aid, parent_id');
            });
    }

    public function links()
    {
        return $this->hasMany('App\Attachment')
            ->where('category', 'link')
            ->whereNotIn('href', function ($query) {
                $query->select('href')
                    ->from('attachments')
                    ->where('post_id', $this->id)
                    ->where('category', 'picture')
                    ->get();
            });
    }

    /**
     * Attachements
     */

    public function attachments()
    {
        return $this->hasMany('App\Attachment');
    }

    public function resolveAttachments(): Collection
    {
        return $this->relations['attachments'] ?? $this->attachments()->get();
    }

    public function getOpenlinkAttribute()
    {
        return $this->resolveAttachments()->firstWhere('category', 'open');
    }

    public function getEmbedsAttribute()
    {
        return $this->resolveAttachments()->where('category', 'embed');
    }

    public function getEmbedAttribute()
    {
        return $this->resolveAttachments()->firstWhere('category', 'embed');
    }

    public function getFilesAttribute()
    {
        return $this->resolveAttachments()->where('category', 'file');
    }

    public function getPicturesAttribute()
    {
        return $this->resolveAttachments()
            ->where('category', 'picture')
            ->where('type', '!=', 'content');
    }

    public function getPictureAttribute()
    {
        return $this->resolveAttachments()->firstWhere('category', 'picture');
    }

    public function getAttachmentAttribute()
    {
        return $this->resolveAttachments()
            ->whereIn('rel', ['enclosure', 'related'])
            ->orderBy('rel', 'desc')
            ->first(); // related first
    }

    public function save(array $options = [])
    {
        try {
            if (!$this->validAtom()) {
                \logError('Invalid Atom: ' . $this->server . '/' . $this->node . '/' . $this->nodeid);

                if ($this->created_at) {
                    $this->delete();
                }

                return;
            }

            if (!$this->changed) return;

            parent::save($options);

            if (!$this->isComment()) {
                $this->healAttachments();
                $this->attachments()->delete();
                $this->attachments()->saveMany($this->attachments);

                $this->tags()->sync($this->tags);
            }
        } catch (\Exception $e) {
            /*
             * When an article is received by two accounts simultaenously
             * in different processes they can be saved using the insert state
             * in the DB causing an error
             */
        }
    }

    private function validAtom(): bool
    {
        return ($this->title != null && $this->updated != null);
    }

    public function scopeRestrictToMicroblog($query)
    {
        return $query->where('posts.node', Post::MICROBLOG_NODE);
    }

    public function scopeRestrictToCommunities($query)
    {
        return $query->where('posts.node', '!=', Post::MICROBLOG_NODE);
    }

    public function scopeWithoutComments($query)
    {
        return $query->whereNull('posts.parent_id');
    }

    public function scopeRestrictUserHost($query)
    {
        $configuration = Configuration::get();

        if ($configuration->restrictsuggestions) {
            $query->whereIn('id', function ($query) {
                $host = me()->session->host;
                $query->select('id')
                    ->from('posts')
                    ->where('server', 'like', '%.' . $host)
                    ->orWhere('server', 'like', '@' . $host);
            });
        }
    }

    public function scopeRestrictReported($query)
    {
        $query->whereNotIn('aid', function ($query) {
            $query->select('reported_id')
                ->from('reported_user')
                ->where('user_id', me()->id);
        });
    }

    public function scopeRestrictNSFW($query)
    {
        $query->where('nsfw', false);

        if (me()->nsfw) {
            $query->orWhere('nsfw', true);
        }
    }

    public function scopeRecents($query)
    {
        $query->join(
            DB::raw('(
            select max(published) as published, server, node
            from posts
            group by server, node) as recents
            '),
            function ($join) {
                $join->on('posts.node', '=', 'recents.node');
                $join->on('posts.published', '=', 'recents.published');
            }
        );
    }

    protected function withContactsScope($query, string $node = Post::MICROBLOG_NODE)
    {
        return $query->unionAll(
            DB::table('posts')
                ->where('node', $node)
                ->whereIn('posts.server', function ($query) {
                    $query->from('rosters')
                        ->select('jid')
                        ->where('session_id', SESSION_ID)
                        ->where('subscription', 'both');
                })
        );
    }

    public function scopeWithContacts($query)
    {
        return $this->withContactsScope($query);
    }

    protected function withMineScope($query, string $node = Post::MICROBLOG_NODE)
    {
        return $query->unionAll(
            DB::table('posts')
                ->where('node', $node)
                ->where('server', me()->id)
        );
    }

    public function scopeWithMine($query)
    {
        return $this->withMineScope($query);
    }

    protected function withSubscriptionsScope($query)
    {
        return $query->unionAll(
            DB::table('posts')
                ->whereIn('server', function ($query) {
                    $query->select('server')
                        ->from('subscriptions')
                        ->where('jid', me()->id);
                })
                ->whereIn('node', function ($query) {
                    $query->select('node')
                        ->from('subscriptions')
                        ->where('jid', me()->id);
                })
        );
    }

    public function scopeWithSubscriptions($query)
    {
        return $this->withSubscriptionsScope($query);
    }

    public function scopeMyStories($query, ?int $id = null)
    {
        $query = $query->whereIn('id', function ($query) {
            $filters = DB::table('posts')->where('id', -1);

            $filters = \App\Post::withMineScope($filters, Post::STORIES_NODE);
            $filters = \App\Post::withContactsScope($filters, Post::STORIES_NODE);

            $query->select('id')->from(
                $filters,
                'posts'
            );
        })
            ->where('published', '>', Carbon::now()->subDay())
            ->orderBy('published', 'desc');

        if ($id != null) $query = $query->where('id', $id);

        return $query;
    }

    public function getColorAttribute(): string
    {
        if ($this->contact) {
            return $this->contact->color;
        }

        if ($this->aid) {
            return stringToColor($this->aid);
        }

        return stringToColor($this->node);
    }

    public function getPreviousAttribute(): ?Post
    {
        return \App\Post::where('server', $this->server)
            ->where('node', $this->node)
            ->where('published', '<', $this->published)
            ->where('open', true)
            ->orderBy('published', 'desc')
            ->first();
    }

    public function getNextAttribute(): ?Post
    {
        return \App\Post::where('server', $this->server)
            ->where('node', $this->node)
            ->where('published', '>', $this->published)
            ->orderBy('published')
            ->where('open', true)
            ->first();
    }

    public function getTruenameAttribute()
    {
        if ($this->contact) {
            return $this->contact->truename;
        }

        return $this->aid
            ? explodeJid($this->aid)['username']
            : '';
    }

    public function getDecodedContentRawAttribute()
    {
        return htmlspecialchars_decode($this->contentraw, ENT_XML1 | ENT_COMPAT);
    }

    private function extractContent(SimpleXMLElement $contents): ?string
    {
        $content = null;

        foreach ($contents as $c) {
            switch ($c->attributes()->type) {
                case 'html':
                    $d = htmlspecialchars_decode((string)$c);

                    $dom = new \DOMDocument('1.0', 'utf-8');
                    $dom->loadHTML('<div>' . $d . '</div>', LIBXML_NOERROR);

                    return (string)$dom->saveHTML($dom->documentElement->lastChild->lastChild);
                    break;
                case 'xhtml':
                    $import = null;
                    $dom = new \DOMDocument('1.0', 'utf-8');

                    if ($c->children() instanceof \DOMElement) {
                        $import = @dom_import_simplexml($c->children());
                    }

                    if ($import == null) {
                        $import = dom_import_simplexml($c);
                    }

                    $element = $dom->importNode($import, true);
                    $dom->appendChild($element);

                    return (string)$dom->saveHTML();
                    break;
                case 'text':
                    if (trim($c) != '') {
                        $this->contentraw = trim($c);
                    }

                    break;
                default:
                    $content = (string)$c;
                    break;
            }
        }

        return $content;
    }

    private function extractTitle($titles): ?string
    {
        $title = null;

        foreach ($titles as $t) {
            switch ($t->attributes()->type) {
                case 'html':
                case 'xhtml':
                    $title = strip_tags(
                        ($t->children()->getName() == 'div' && (string)$t->children()->attributes()->xmlns == 'http://www.w3.org/1999/xhtml')
                            ? html_entity_decode((string)$t->children()->asXML())
                            : (string)$t->children()->asXML()
                    );
                    break;
                case 'text':
                    if (trim($t) != '') {
                        $title = trim($t);
                    }
                    break;
                default:
                    $title = (string)$t;
                    break;
            }
        }

        return $title;
    }

    public function set($entry, $delay = false)
    {
        $this->nodeid = (string)$entry->attributes()->id;

        $hash = hash('sha256', $entry->asXML());

        // Detect if things changed from the cached version
        if ($hash == $this->contenthash) {
            return \React\Promise\resolve();
        }

        $this->contenthash = $hash;
        $this->changed = true;

        // Ensure that the author is the publisher
        if (
            $entry->entry->author && $entry->entry->author->uri
            && 'xmpp:' . baseJid((string)$entry->attributes()->publisher) == (string)$entry->entry->author->uri
        ) {
            $this->aid = substr((string)$entry->entry->author->uri, 5);
            $this->aname = ($entry->entry->author->name)
                ? (string)$entry->entry->author->name
                : null;

            $this->aemail = ($entry->entry->author->email)
                ? (string)$entry->entry->author->email
                : null;
        } else {
            $this->aid = null;
        }

        if (empty($this->aname)) {
            $this->aname = null;
        }

        // Content
        $this->title = $entry->entry->title
            ? $this->extractTitle($entry->entry->title)
            : null;

        $summary = ($entry->entry->summary && (string)$entry->entry->summary != '')
            ? '<p class="summary">' . (string)$entry->entry->summary . '</p>'
            : null;

        $content = $entry->entry->content
            ? $this->extractContent($entry->entry->content)
            : null;

        $this->content = $this->contentcleaned = null;

        if ($summary != null || $content != null) {
            $this->content = trim((string)$summary . (string)$content);
            $this->contentcleaned = purifyHTML(html_entity_decode($this->content));//requestAPI('purifyhtml', post: ['content' => $this->content]);
        }

        $this->updated = ($entry->entry->updated)
            ? toSQLDate($entry->entry->updated)
            : null;

        if ($entry->entry->published) {
            $this->published = toSQLDate($entry->entry->published);
        } elseif ($entry->entry->updated) {
            $this->published = toSQLDate($entry->entry->updated);
        } else {
            $this->published = gmdate(MOVIM_SQL_DATE);
        }

        if ($delay) {
            $this->delay = $delay;
        }

        // Tags parsing
        if ($entry->entry->category) {
            if (
                $entry->entry->category->count() == 1
                && isset($entry->entry->category->attributes()->term)
                && !empty(trim($entry->entry->category->attributes()->term))
            ) {
                $tag = \App\Tag::firstOrCreateSafe([
                    'name' => strtolower((string)$entry->entry->category->attributes()->term)
                ]);

                $this->tags[] = $tag->id;

                if ($tag->name == 'nsfw') {
                    $this->nsfw = true;
                }
            } else {
                foreach ($entry->entry->category as $cat) {
                    if (!empty(trim((string)$cat->attributes()->term))) {
                        $tag = \App\Tag::firstOrCreateSafe([
                            'name' => strtolower((string)$cat->attributes()->term)
                        ]);

                        if ($tag) {
                            $this->tags[] = $tag->id;

                            if ($tag->name == 'nsfw') {
                                $this->nsfw = true;
                            }
                        }
                    }
                }
            }
        }

        // Extract more tags if possible
        $tagsContent = getHashtags(htmlspecialchars($this->title ?? ''))
            + getHashtags(htmlspecialchars($this->contentraw ?? ''));
        foreach ($tagsContent as $tag) {
            $tag = \App\Tag::firstOrCreateSafe([
                'name' => strtolower((string)$tag)
            ]);

            $this->tags[] = $tag->id;
        }

        if (current(explode('.', $this->server)) == 'nsfw') {
            $this->nsfw = true;
        }

        if (!isset($this->commentserver)) {
            $this->commentserver = $this->server;
        }

        // We fill empty aid
        if ($this->isMicroblog() && empty($this->aid)) {
            $this->aid = $this->server;
        }

        // We check if this is a reply
        if ($entry->entry->{'in-reply-to'}) {
            $href = (string)$entry->entry->{'in-reply-to'}->attributes()->href;
            $arr = explode(';', $href);
            $this->replyserver = substr($arr[0], 5, -1);
            $this->replynode = substr($arr[1], 5);
            $this->replynodeid = substr($arr[2], 5);
        }

        $extra = false;
        // We try to extract a picture
        $xml = \simplexml_load_string('<div>' . $this->contentcleaned . '</div>');
        if ($xml) {
            $results = $xml->xpath('//img/@src');

            if (is_array($results) && !empty($results)) {
                $extra = (string)$results[0];
            } else {
                $results = $xml->xpath('//video/@poster');
                if (is_array($results) && !empty($results)) {
                    $extra = (string)$results[0];
                }
            }

            $results = $xml->xpath('//a');
            if (is_array($results) && !empty($results)) {
                foreach ($results as $link) {
                    $link->addAttribute('target', '_blank');
                }
            }
        }

        $this->like = $this->isLike();
        $this->open = false;

        if ($this->isComment()) {
            $p = \App\Post::where('commentserver', $this->server)
                ->where('commentnodeid', substr($this->node, 30))
                ->first();

            if ($p) {
                $this->parent_id = $p->id;
            }
        }

        $this->setAttachments($entry->entry->link, $extra);
    }

    private function setAttachments($links, $extra = false)
    {
        $picture = false;

        foreach ($links as $attachment) {
            $enc = (array)$attachment->attributes();
            $enc = $enc['@attributes'];

            $att = new Attachment;

            if (empty($enc['href'])) {
                continue;
            }

            $att->rel = $enc['rel'] ?? 'alternate';
            $att->href = $enc['href'];
            $att->category = 'other';

            if (isset($enc['title'])) {
                $att->title = $enc['title'];
            }
            if (isset($enc['description'])) {
                $att->description = $enc['description'];
            }

            switch ($att->rel) {
                case 'enclosure':
                    if (isset($enc['type'])) {
                        $att->category = 'file';
                        $att->type = $enc['type'];

                        if (typeIsPicture($enc['type'])) {
                            $att->category = 'picture';
                            $picture = true;
                        }
                    }
                    break;
                case 'alternate':
                    if (Validator::url()->isValid($enc['href'])) {
                        $this->open = true;
                        $att->category = 'open';
                    }
                    break;
                case 'related':
                    $att->category = 'link';
                    $att->logo = (isset($enc['logo'])) ? $enc['logo'] : null;
                    break;
            }

            if (in_array($att->rel, ['enclosure', 'related', 'alternate'])) {
                $atte = new Attachment;
                $atte->rel = $att->rel;
                $atte->category = 'embed';

                // Youtube
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $enc['href'], $match)) {
                    $atte->href = 'https://www.youtube.com/embed/' . $match[1];
                    $this->attachments[] = $atte;
                    // RedGif
                } elseif (preg_match('/(?:https:\/\/)?(?:www.)?redgifs.com\/watch\/([a-zA-Z]+)$/', $enc['href'], $match)) {
                    $atte->href = 'https://www.redgifs.com/ifr/' . $match[1];
                    $this->attachments[] = $atte;
                    $this->resolveUrl($enc['href']);
                    // PeerTube
                } elseif (
                    preg_match('/https:\/\/?(.*)\/w\/(\w{22})/', $enc['href'], $match)
                    || preg_match('/https:\/\/?(.*)\/videos\/watch\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/', $enc['href'], $match)
                ) {
                    $atte->href = 'https://' . $match[1] . '/videos/embed/' . $match[2];
                    $this->attachments[] = $atte;
                    // Reddit
                } elseif (
                    in_array(parse_url($enc['href'], PHP_URL_HOST), ['old.reddit.com', 'reddit.com', 'www.reddit.com'])
                    && substr(parse_url($enc['href'], PHP_URL_PATH), 0, 8) == '/gallery'
                ) {
                    $this->resolveUrl($enc['href']);
                }
            }

            $this->attachments[] = $att;

            if ((string)$attachment->attributes()->title == 'comments') {
                $url = parse_url(urldecode((string)$attachment->attributes()->href));

                if ($url) {
                    $this->commentserver = $url['path'];
                    $this->commentnodeid = substr($url['query'], 36);
                }
            }
        }

        if ($picture == false && $extra) {
            $attachment = new Attachment;
            $attachment->rel = 'enclosure';
            $attachment->href = $extra;
            $attachment->type = 'content';
            $attachment->category = 'picture';
            $this->attachments[] = $attachment;
        }
    }

    private function resolveUrl(string $url)
    {
        array_push(
            $this->resolvableAttachments,
            new Promise(function () use ($url) {
                \requestResolverWorker($url)->then(function ($extractor) {
                    try {
                        $atte = new Attachment;
                        $atte->rel = 'enclosure';
                        $atte->href = $extractor->image;
                        $atte->type = 'media/jpeg';
                        $atte->category = 'picture';
                        $atte->post_id = $this->id;
                        $atte->save();
                    } catch (\Throwable $th) {
                        //
                    }

                    Wrapper::getInstance()->iterate('post_resolved', (new Packet)->pack($this->id));
                });
            })
        );
    }

    private function healAttachments()
    {
        $enclosures = [];

        foreach (array_filter($this->attachments, fn($a) => $a->rel == 'enclosure') as $attachment) {
            array_push($enclosures, $attachment->href);
        }

        foreach (array_filter($this->attachments, fn($a) => $a->rel != 'enclosure') as $key => $attachment) {
            if (in_array($attachment->href, $enclosures)) {
                unset($this->attachments[$key]);
            }
        }

        // Remove duplicates...
        foreach ($this->attachments as $key => $attachment) {
            foreach ($this->attachments as $keyCheck => $attachmentCheck) {
                if (
                    $key != $keyCheck
                    && $attachment->href == $attachmentCheck->href
                    && $attachment->category == $attachmentCheck->category
                    && $attachment->rel == $attachmentCheck->rel
                ) {
                    unset($this->attachments[$key]);
                }
            }
        }
    }

    public function getUUID()
    {
        if (substr($this->nodeid, 10) == 'urn:uuid:') {
            return $this->nodeid;
        }

        return 'urn:uuid:' . generateUUID(hash('sha256', $this->server . $this->node . $this->nodeid, true));
    }

    public function getRef(): string
    {
        return 'xmpp:' . $this->server . '?;node=' . $this->node . ';item=' . $this->nodeid;
    }

    public function getLink(?bool $public = false): string
    {
        if ($public) {
            return $this->isMicroblog()
                ? \Movim\Route::urlize('blog', [$this->server, $this->nodeid])
                : \Movim\Route::urlize('community', [$this->server, $this->node, $this->nodeid]);
        }

        return \Movim\Route::urlize('post', [$this->server, $this->node, $this->nodeid]);
    }

    // Works only for the microblog posts
    public function getParent(): ?Post
    {
        return \App\Post::find($this->parent_id);
    }

    public function isMine(User $me, ?bool $force = false): bool
    {
        if ($force) {
            return ($this->aid == $me->id);
        }

        return ($this->aid == $me->id
            || $this->server == $me->id);
    }

    public function isMicroblog(): bool
    {
        return ($this->node == "urn:xmpp:microblog:0");
    }

    public function isEdited(): bool
    {
        return $this->published != $this->updated;
    }

    public function isEditable(): bool
    {
        return ($this->contentraw != null || $this->links != null);
    }

    public function isShort(): bool
    {
        return $this->contentcleaned == null || (strlen($this->contentcleaned) < 700);
    }

    public function isBrief(): bool
    {
        return ($this->content == null && strlen($this->title) < $this->titleLimit);
    }

    public function isReply(): bool
    {
        return isset($this->replynodeid);
    }

    public function isLike(): bool
    {
        return ($this->title == '♥');
    }

    public function isRTL(): bool
    {
        return (isRTL($this->contentraw ?? '') || isRTL($this->title ?? ''));
    }

    public function isStory(): bool
    {
        return $this->node == Post::STORIES_NODE;
    }

    public function isComment(): bool
    {
        return (str_starts_with($this->node, Post::COMMENTS_NODE));
    }

    public function hasCommentsNode(): bool
    {
        return (isset($this->commentserver)
            && isset($this->commentnodeid));
    }

    public function getSummary()
    {
        if ($this->isBrief()) {
            return truncate(html_entity_decode($this->title), 140);
        }

        return truncate(stripTags(html_entity_decode($this->contentcleaned ?? '')), 140);
    }

    public function getContent(bool $addHashTagLinks = false): string
    {
        if ($this->contentcleaned == null) return '';

        return ($addHashTagLinks)
            ? addHashtagsLinks($this->contentcleaned)
            : $this->contentcleaned;
    }

    public function getReply()
    {
        if (!$this->replynodeid) {
            return;
        }

        return \App\Post::where('server', $this->replyserver)
            ->where('node', $this->replynode)
            ->where('nodeid', $this->replynodeid)
            ->first();
    }

    public function isLiked()
    {
        return ($this->likes()->where('aid', me()->id)->count() > 0);
    }

    public function isRecycled()
    {
        return false;
    }
}

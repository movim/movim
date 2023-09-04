<?php

namespace App;

use Respect\Validation\Validator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Post extends Model
{
    protected $primaryKey = 'id';

    protected $guarded = [];
    public $with = ['attachments', 'likes', 'comments',
                    'contact',  'openlink', 'embed',
                    'links', 'files', 'pictures', 'picture',
                    'attachment'];
    public $withCount = ['userViews'];

    private $titleLimit = 200;
    private $changed = false; // Detect if the set post was different from the cache

    public $attachments = [];
    public $tags = [];

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
        return $this->hasOne('App\Info', 'server', 'server')
                    ->where('node', $this->node);
    }

    public function userViews()
    {
        return $this->belongsToMany(User::class, 'post_user_views', 'post_id', 'user_id')->withTimestamps();
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

    public function openlink()
    {
        return $this->hasOne('App\Attachment')
                    ->where('category', 'open');
    }

    public function embed()
    {
        return $this->hasOne('App\Attachment')
                    ->where('category', 'embed');
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

    public function files()
    {
        return $this->hasMany('App\Attachment')
                    ->where('category', 'file');
    }

    public function pictures()
    {
        return $this->hasMany('App\Attachment')
                    ->where('category', 'picture')
                    ->where('type', '!=', 'content');
    }

    public function picture()
    {
        return $this->hasOne('App\Attachment')
                    ->where('category', 'picture');
    }

    public function attachment()
    {
        return $this->hasOne('App\Attachment')
                    ->whereIn('rel', ['enclosure', 'related'])
                    ->orderBy('rel', 'desc'); // related first
    }

    public function attachments()
    {
        return $this->hasMany('App\Attachment');
    }

    public function save(array $options = [])
    {
        try {
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

    public function scopeRestrictToMicroblog($query)
    {
        return $query->where('posts.node', 'urn:xmpp:microblog:0');
    }

    public function scopeRestrictToCommunities($query)
    {
        return $query->where('posts.node', '!=', 'urn:xmpp:microblog:0');
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
                $host = \App\User::me()->session->host;
                $query->select('id')
                      ->from('posts')
                      ->where('server', 'like', '%.' . $host)
                      ->orWhere('server', 'like', '@' . $host);
            });
        }
    }

    public function scopeRestrictNSFW($query)
    {
        $query->where('nsfw', false);

        if (\App\User::me()->nsfw) {
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

    protected function withContactsScope($query)
    {
        return $query->unionAll(DB::table('posts')
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

    protected function withMineScope($query)
    {
        return $query->unionAll(DB::table('posts')
            ->where('node', 'urn:xmpp:microblog:0')
            ->where('server', \App\User::me()->id)
        );
    }

    public function scopeWithMine($query)
    {
        return $this->withMineScope($query);
    }

    protected function withSubscriptionsScope($query)
    {
        return $query->unionAll(DB::table('posts')
            ->whereIn('server', function ($query) {
                $query->select('server')
                    ->from('subscriptions')
                    ->where('jid', \App\User::me()->id);
            })
            ->whereIn('node', function ($query) {
                $query->select('node')
                    ->from('subscriptions')
                    ->where('jid', \App\User::me()->id);
            })
        );
    }

    public function scopeWithSubscriptions($query)
    {
        return $this->withSubscriptionsScope($query);
    }

    public function getPreviousAttribute()
    {
        return \App\Post::where('server', $this->server)
                       ->where('node', $this->node)
                       ->where('published', '<', $this->published)
                       ->where('open', true)
                       ->orderBy('published', 'desc')
                       ->first();
    }

    public function getNextAttribute()
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

    private function extractContent($contents)
    {
        $content = '';

        foreach ($contents as $c) {
            switch ($c->attributes()->type) {
                case 'html':
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

    private function extractTitle($titles)
    {
        $title = '';
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
            return;
        } else {
            $this->contenthash = $hash;
            $this->changed = true;
        }

        // Ensure that the author is the publisher
        if ($entry->entry->author && $entry->entry->author->uri
        && 'xmpp:'.baseJid((string)$entry->attributes()->publisher) == (string)$entry->entry->author->uri) {
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

        // Non standard support
        /*if ($entry->entry->source && $entry->entry->source->author->name) {
            $this->aname = (string)$entry->entry->source->author->name;
        }
        if ($entry->entry->source && $entry->entry->source->author->uri
         && substr((string)$entry->entry->source->author->uri, 5) == 'xmpp:') {
            $this->aid = substr((string)$entry->entry->source->author->uri, 5);
        }*/

        if (empty($this->aname)) {
            $this->aname = null;
        }

        // Content
        $summary = ($entry->entry->summary && (string)$entry->entry->summary != '')
            ? '<p class="summary">'.(string)$entry->entry->summary.'</p>'
            : null;

        $content = '';

        if ($entry->entry->content) {
            $content = $this->extractContent($entry->entry->content);
            $this->title = $this->extractTitle($entry->entry->title);
        } else {
            $content = $this->extractContent($entry->entry->title);
        }

        $content = $summary.$content;

        $this->updated = ($entry->entry->updated)
            ? toSQLDate($entry->entry->updated)
            : gmdate(MOVIM_SQL_DATE);

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
            if ($entry->entry->category->count() == 1
            && isset($entry->entry->category->attributes()->term)
            && !empty(trim($entry->entry->category->attributes()->term))) {
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

        // Save the base and cleaned content
        $this->content = trim($content);
        $this->contentcleaned = requestAPI('purifyhtml', 2, ['content' => $this->content]);

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
        $xml = \simplexml_load_string('<div>'.$this->contentcleaned.'</div>');
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
        $this->setAttachments($entry->entry->link, $extra);

        if ($this->isComment()) {
            $p = \App\Post::where('commentserver', $this->server)
                          ->where('commentnodeid', substr($this->node, 30))
                          ->first();

            if ($p) {
                $this->parent_id = $p->id;
            }
        }
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
                    if (Validator::url()->validate($enc['href'])) {
                        $this->open = true;
                        $att->category = 'open';
                    }
                    break;
                case 'related':
                    $att->category = 'link';
                    $att->logo = (isset($enc['logo'])) ? $enc['logo'] : null;
                    break;
            }

            if (in_array($att->rel, ['enclosure', 'related'])) {
                $atte = new Attachment;
                $atte->rel = $enc['rel'];
                $atte->category = 'embed';

                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $enc['href'], $match)) {
                    $atte->href = 'https://www.youtube.com/embed/' . $match[1];
                    $this->attachments[] = $atte;
                } elseif (preg_match('/(?:https:\/\/)?(?:www.)?redgifs.com\/watch\/([a-zA-Z]+)$/', $enc['href'], $match)) {
                    $atte->href = 'https://www.redgifs.com/ifr/' . $match[1];
                    $this->attachments[] = $atte;
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

    private function healAttachments()
    {
        $enclosures = [];

        foreach (array_filter($this->attachments, fn ($a) => $a->rel == 'enclosure') as $attachment)
        {
            array_push($enclosures, $attachment->href);
        }

        foreach (array_filter($this->attachments, fn ($a) => $a->rel != 'enclosure') as $key => $attachment)
        {
            if (in_array($attachment->href, $enclosures)) {
                unset($this->attachments[$key]);
            }
        }

        // Remove duplicates...
        foreach ($this->attachments as $key => $attachment) {
            foreach($this->attachments as $keyCheck => $attachmentCheck) {
                if ($key != $keyCheck
                && $attachment->href == $attachmentCheck->href
                && $attachment->category == $attachmentCheck->category
                && $attachment->rel == $attachmentCheck->rel) {
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

        return 'urn:uuid:'.generateUUID($this->nodeid);
    }

    public function getRef()
    {
        return 'xmpp:'.$this->server.'?;node='.$this->node.';item='.$this->nodeid;
    }

    // Works only for the microblog posts
    public function getParent()
    {
        return \App\Post::find($this->parent_id);
    }

    public function isMine($force = false)
    {
        if ($force) {
            return ($this->aid == \App\User::me()->id);
        }

        return ($this->aid == \App\User::me()->id
            || $this->server == \App\User::me()->id);
    }

    public function isMicroblog()
    {
        return ($this->node == "urn:xmpp:microblog:0");
    }

    public function isEditable()
    {
        return ($this->contentraw != null || $this->links != null);
    }

    public function isShort()
    {
        return (strlen($this->contentcleaned) < 700);
    }

    public function isBrief()
    {
        return ($this->content == '' && strlen($this->title) < $this->titleLimit);
    }

    public function isReply()
    {
        return isset($this->replynodeid);
    }

    public function isLike()
    {
        return ($this->contentraw == '♥' || $this->title == '♥');
    }

    public function isRTL()
    {
        return (isRTL($this->contentraw ?? '') || isRTL($this->title ?? ''));
    }

    public function isComment()
    {
        return (substr($this->node, 0, 30) == 'urn:xmpp:microblog:0:comments/');
    }

    public function hasCommentsNode()
    {
        return (isset($this->commentserver)
             && isset($this->commentnodeid));
    }

    public function getSummary()
    {
        if ($this->isBrief()) {
            return truncate(html_entity_decode($this->title), 140);
        }

        return truncate(stripTags(html_entity_decode($this->contentcleaned)), 140);
    }

    public function getTitle()
    {
        return ($this->title == null
            || strlen($this->title) >= $this->titleLimit)
            ? __('post.default_title')
            : $this->title;
    }

    public function getContent()
    {
        return ($this->title && strlen($this->title) >= $this->titleLimit)
            ? nl2br(addUrls(addHashtagsLinks($this->title)))
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
        return ($this->likes()->where('aid', \App\User::me()->id)->count() > 0);
    }

    public function isRecycled()
    {
        return false;
    }
}

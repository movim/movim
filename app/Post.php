<?php

namespace App;

use Respect\Validation\Validator;

use Movim\Picture;
use Movim\User;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $primaryKey = 'id';

    protected $guarded = [];
    private $titleLimit = 200;

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
                    ->where('like', false);
    }

    public function likes()
    {
        return $this->hasMany('App\Post', 'parent_id', 'id')
                    ->where('like', true);
    }

    public function attachments()
    {
        return $this->hasMany('App\Attachment');
    }

    public function save(array $options = [])
    {
        parent::save($options);

        $this->attachments()->delete();
        $this->attachments()->saveMany($this->attachments);
        $this->tags()->sync($this->tags);
    }

    public function getOpenlinkAttribute()
    {
        if (!$this->open) return;
        return $this->attachments()->where('category', 'open')->first();
    }

    public function getYoutubeAttribute()
    {
        return $this->attachments()->where('category', 'youtube')->first();
    }

    public function getLinksAttribute()
    {
        return $this->attachments()->where('category', 'link')->get();
    }

    public function getFilesAttribute()
    {
        return $this->attachments()->where('category', 'file')->get();
    }

    public function getPicturesAttribute()
    {
        return $this->attachments()->where('category', 'picture')->get();
    }

    public function getPictureAttribute()
    {
        return $this->attachments()->where('category', 'picture')->first();
    }

    public function getPreviousAttribute()
    {
        return \App\Post::where('server', $this->server)
                       ->where('node', $this->node)
                       ->where('published', '<', $this->published)
                       ->orderBy('published', 'desc')
                       ->first();
    }

    public function getNextAttribute()
    {
        return \App\Post::where('server', $this->server)
                       ->where('node', $this->node)
                       ->where('published', '>', $this->published)
                       ->orderBy('published')
                       ->first();
    }

    public function getTruenameAttribute()
    {
        if ($this->contact) {
            return $this->contact->truename;
        }

        $truename = explodeJid($this->aid);
        return (isset($truename['username'])) ? $truename['username'] : '';
    }

    private function extractContent($contents)
    {
        $content = '';

        foreach($contents as $c) {
            switch($c->attributes()->type) {
                case 'html':
                case 'xhtml':
                    $dom = new \DOMDocument('1.0', 'utf-8');
                    $import = @dom_import_simplexml($c->children());
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
                default :
                    $content = (string)$c;
                    break;
            }
        }

        return $content;
    }

    private function extractTitle($titles)
    {
        $title = '';
        foreach($titles as $t) {
            switch($t->attributes()->type) {
                case 'html':
                case 'xhtml':
                    $title = strip_tags((string)$t->children()->asXML());
                    break;
                case 'text':
                    if (trim($t) != '') {
                        $title = trim($t);
                    }
                    break;
                default :
                    $title = (string)$t;
                    break;
            }
        }

        return $title;
    }

    public function set($item, $from, $delay = false, $node = false)
    {
        $entry = ($item->item) ? $item->item : $item;

        if ($from != '') {
            $this->server = $from;
        }

        $this->node = ($node) ? $node : (string)$item->attributes()->node;

        $this->nodeid = (string)$entry->attributes()->id;

        if ($entry->entry->id)
            $this->nodeid = (string)$entry->entry->id;

        // Get some informations on the author
        if ($entry->entry->author->name)
            $this->aname = (string)$entry->entry->author->name;
        if ($entry->entry->author->uri)
            $this->aid = substr((string)$entry->entry->author->uri, 5);
        if ($entry->entry->author->email)
            $this->aemail = (string)$entry->entry->author->email;

        // Non standard support
        if ($entry->entry->source && $entry->entry->source->author->name)
            $this->aname = (string)$entry->entry->source->author->name;
        if ($entry->entry->source && $entry->entry->source->author->uri)
            $this->aid = substr((string)$entry->entry->source->author->uri, 5);

        if (empty($this->aname)) $this->aname = null;

        $this->title = $this->extractTitle($entry->entry->title);

        // Content
        $summary = ($entry->entry->summary && (string)$entry->entry->summary != '')
            ? '<p class="summary">'.(string)$entry->entry->summary.'</p>'
            : null;

        if ($entry->entry && $entry->entry->content) {
            $content = $this->extractContent($entry->entry->content);
        } elseif ($summary == '') {
            $content = __('');
        } else {
            $content = '';
        }

        $content = $summary.$content;

        $this->updated = ($entry->entry->updated)
            ? (string)$entry->entry->updated
            : gmdate(SQL::SQL_DATE);

        if ($entry->entry->published) {
            $this->published = (string)$entry->entry->published;
        } elseif ($entry->entry->updated) {
            $this->published = (string)$entry->entry->updated;
        } else {
            $this->published = gmdate(SQL::SQL_DATE);
        }

        if ($delay) $this->delay = $delay;

        // Tags parsing
        if ($entry->entry->category) {
            if ($entry->entry->category->count() == 1
            && isset($entry->entry->category->attributes()->term)) {
                $tag = \App\Tag::firstOrCreate([
                    'name' => strtolower((string)$entry->entry->category->attributes()->term)
                ]);

                $this->tags[] = $tag->id;

                if ($tag->name == 'nsfw') $this->nsfw = true;
            } else {
                foreach($entry->entry->category as $cat) {
                    $tag = \App\Tag::firstOrCreate([
                        'name' => strtolower((string)$cat->attributes()->term)
                    ]);

                    $this->tags[] = $tag->id;

                    if ($tag->tag == 'nsfw') $this->nsfw = true;
                }
            }
        }

        if (current(explode('.', $this->server)) == 'nsfw') $this->nsfw = true;

        if (!isset($this->commentserver)) {
            $this->commentserver = $this->server;
        }

        $this->content = trim($content);
        $this->contentcleaned = purifyHTML(html_entity_decode($this->content));

        // We fill empty aid
        if ($this->isMicroblog() && empty($this->aid)) {
            $this->aid = $this->server;
        }

        // We check if this is a reply
        if ($entry->entry->{'in-reply-to'}) {
            $href = (string)$entry->entry->{'in-reply-to'}->attributes()->href;
            $arr = explode(';', $href);
            $reply = [
                'server' => substr($arr[0], 5, -1),
                'node'   => substr($arr[1], 5),
                'nodeid' => substr($arr[2], 5)
            ];

            $this->reply = $reply;
        }

        $extra = false;
        // We try to extract a picture
        $xml = \simplexml_load_string('<div>'.$this->contentcleaned.'</div>');
        if ($xml) {
            $results = $xml->xpath('//img/@src');

            if (is_array($results) && !empty($results)) {
                $extra = (string)$results[0];
                //$this->setAttachments($entry->entry->link, $extra);
            } else {
                $results = $xml->xpath('//video/@poster');
                if (is_array($results) && !empty($results)) {
                    $extra = (string)$results[0];
                    //$this->setAttachments($entry->entry->link, $extra);
                }
            }

            $results = $xml->xpath('//a');
            if (is_array($results) && !empty($results)) {
                foreach($results as $link) {
                    $link->addAttribute('target', '_blank');
                }
            }
        }

        $this->like = $this->isLike();

        $this->setAttachments($entry->entry->link, $extra);

        if ($this->isComment()) {
            /*$pd = new \Modl\PostnDAO;
            $p = $pd->getParent($this->server, substr($this->node, 30));
            if ($p) {
                $this->parentserver = $p->server;
                $this->parentnode   = $p->node;
                $this->parentnodeid = $p->nodeid;
            }*/
        }
    }

    private function setAttachments($links, $extra = false)
    {
        /*if ($extra) {
            $attachment = new \App\Attachment;
            $attachment->rel = 'enclosure';
            $attachment->href = protectPicture($extra);
            $attachment->category = 'picture';
            $this->attachments[] = $attachment;
        }*/

        foreach($links as $attachment) {
            $enc = (array)$attachment->attributes();
            $enc = $enc['@attributes'];

            $att = new \App\Attachment;

            if (empty($enc['href']) || empty($enc['rel'])) {
                continue;
            }

            $att->rel = $enc['rel'];
            $att->href = $enc['href'];
            $att->category = 'other';

            if (isset($enc['title'])) $att->title = $enc['title'];
            if (isset($enc['description'])) $att->description = $enc['description'];

            switch ($enc['rel']) {
                case 'enclosure':
                    $att->category = 'file';
                    $att->type = $enc['type'];

                    if (typeIsPicture($enc['type'])) {
                        $att->category = 'picture';
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

                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $enc['href'], $match)) {
                        $atty = new \App\Attachment;
                        $atty->rel = $enc['rel'];
                        $atty->href = 'https://www.youtube.com/embed/' . $match[1];
                        $atty->category = 'youtube';
                        $this->attachments[] = $atty;
                    }
                    break;
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
    }

    public function getAttachments()
    {
        $attachments = null;
        $this->openlink = null;

        if (isset($this->links)) {
            $links = $this->links;
            $attachments = [
                'pictures' => [],
                'files' => [],
                'links' => []
            ];

            foreach($links as $l) {
                // If the href is not a valid URL we skip
                if (!Validator::url()->validate($l['href'])) continue;

                // Prepare the switch
                $rel = isset($l['rel']) ? $l['rel'] : null;
                switch($rel) {
                    case 'enclosure':
                        if (typeIsPicture($l['type'])) {
                            array_push($attachments['pictures'], $l);
                        } elseif ($l['type'] != 'picture') {
                            array_push($attachments['files'], $l);
                        }
                        break;

                    case 'related':
                        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $l['href'], $match)) {
                            $this->youtube = $match[1];
                        }

                        array_push(
                            $attachments['links'],
                            [
                                'href' => $l['href'],
                                'url'  => parse_url($l['href']),
                                'title'=> (isset($l['title'])) ? $l['title'] : false,
                                'rel'  => 'related',
                                'description' => (isset($l['description'])) ? $l['description'] : false,
                                'logo' => (isset($l['logo'])) ? $l['logo'] : false
                            ]
                        );
                        break;

                    case 'alternate':
                    default:
                        $this->openlink = $l['href'];
                        if (!$this->isMicroblog()) {
                            array_push(
                                $attachments['links'],
                                [
                                    'href' => $l['href'],
                                    'url'  => parse_url($l['href']),
                                    'rel'  => 'alternate'
                                ]
                            );
                        }
                        break;
                }
            }
        }

        if (empty($attachments['pictures'])) unset($attachments['pictures']);
        if (empty($attachments['files']))    unset($attachments['files']);
        if (empty($attachments['links']))    unset($attachments['links']);

        return $attachments;
    }

    public function getAttachment()
    {
        $attachments = $this->getAttachments();

        if (array_key_exists('links', $attachments)) {
            foreach($attachments['links'] as $attachment) {
                if (in_array($attachment['rel'], ['enclosure', 'related'])) {
                    return $attachment;
                }
            }
        }

        unset($attachments['links']);

        foreach($attachments as $key => $group) {
            foreach($group as $attachment) {
                if (in_array($attachment['rel'], ['enclosure', 'related'])) {
                    return $attachment;
                }
            }
        }
    }

    public function getPicture()
    {
        return $this->picture;
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
        return isset($this->reply);
    }

    public function isLike()
    {
        return ($this->contentraw == '♥' || $this->title == '♥');
    }

    public function isRTL()
    {
        return (isRTL($this->contentraw) || isRTL($this->title));
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
        return (isset($this->title)
            && strlen($this->title) >= $this->titleLimit)
            ? __('post.default_title')
            : $this->title;
    }

    public function getContent()
    {
        return (strlen($this->title) >= $this->titleLimit)
            ? nl2br(addUrls(addHashtagsLinks($this->title)))
            : $this->contentcleaned;
    }

    public function getReply()
    {
        if (!$this->reply) return;

        $reply = $this->reply;
        $pd = new \Modl\PostnDAO;
        return $pd->get($reply['server'], $reply['node'], $reply['nodeid']);
    }

    public function countComments()
    {
        return $this->comments()->count();
    }

    public function countLikes()
    {
        return $this->likes()->count();
    }

    public function isLiked()
    {
        return ($this->likes()->where('aid', \App\User::me()->id)->count() > 0);
    }

    public function isRecycled()
    {
        return false;
    }

    /*public function countReplies()
    {
        return $pd->countReplies([
            'server'    => $this->server,
            'node'      => $this->node,
            'nodeid'    => $this->nodeid
        ]);
    }*/
}


<?php

namespace Modl;

use Respect\Validation\Validator;

class Postn extends Model {
    public $origin;         // Where the post is comming from (jid or server)
    public $node;           // microblog or pubsub
    public $nodeid;         // the ID if the item

    public $aname;          // author name
    public $aid;            // author id
    public $aemail;         // author email

    public $title;          //
    public $content;        // The content
    public $contentraw;     // The raw content
    public $contentcleaned; // The cleanned content

    public $commentplace;

    public $published;      //
    public $updated;        //
    public $delay;          //

    public $picture;        // Tell if the post contain embeded pictures

    public $lat;
    public $lon;

    public $links;

    public $hash;

    private $youtube;

    public $open;
    private $openlink;

    public function __construct() {
        $this->hash = md5(openssl_random_pseudo_bytes(5));

        $this->_struct = '
        {
            "origin" :
                {"type":"string", "size":64, "key":true },
            "node" :
                {"type":"string", "size":96, "key":true },
            "nodeid" :
                {"type":"string", "size":96, "key":true },
            "aname" :
                {"type":"string", "size":128 },
            "aid" :
                {"type":"string", "size":64 },
            "aemail" :
                {"type":"string", "size":64 },
            "title" :
                {"type":"text" },
            "content" :
                {"type":"text" },
            "contentraw" :
                {"type":"text" },
            "contentcleaned" :
                {"type":"text" },
            "commentplace" :
                {"type":"string", "size":128 },

            "open" :
                {"type":"bool"},

            "published" :
                {"type":"date" },
            "updated" :
                {"type":"date" },
            "delay" :
                {"type":"date" },

            "lat" :
                {"type":"string", "size":32 },
            "lon" :
                {"type":"string", "size":32 },

            "links" :
                {"type":"text" },
            "picture" :
                {"type":"int", "size":4 },
            "hash" :
                {"type":"string", "size":128, "mandatory":true }
        }';

        parent::__construct();
    }

    private function getContent($contents) {
        $content = '';
        foreach($contents as $c) {
            switch($c->attributes()->type) {
                case 'html':
                case 'xhtml':
                    $dom = new \DOMDocument('1.0', 'utf-8');
                    $import = @dom_import_simplexml($c->children());
                    if($import == null) {
                        $import = dom_import_simplexml($c);
                    }
                    $element = $dom->importNode($import, true);
                    $dom->appendChild($element);
                    return (string)$dom->saveHTML();
                    break;
                case 'text':
                    if(trim($c) != '') {
                        $this->__set('contentraw', trim($c));
                    }
                    break;
                default :
                    $content = (string)$c;
                    break;
            }
        }

        return $content;
    }

    private function getTitle($titles) {
        $title = '';
        foreach($titles as $t) {
            switch($t->attributes()->type) {
                case 'html':
                case 'xhtml':
                    $title = strip_tags((string)$t->children()->asXML());
                    break;
                case 'text':
                    if(trim($t) != '') {
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

    public function set($item, $from, $delay = false, $node = false) {
        if($item->item)
            $entry = $item->item;
        else
            $entry = $item;

        if($from != '')
            $this->__set('origin', $from);

        if($node)
            $this->__set('node', $node);
        else
            $this->__set('node', (string)$item->attributes()->node);

        $this->__set('nodeid', (string)$entry->attributes()->id);

        if($entry->entry->id)
            $this->__set('nodeid', (string)$entry->entry->id);

        // Get some informations on the author
        if($entry->entry->author->name)
            $this->__set('aname', (string)$entry->entry->author->name);
        if($entry->entry->author->uri)
            $this->__set('aid', substr((string)$entry->entry->author->uri, 5));
        if($entry->entry->author->email)
            $this->__set('aemail', (string)$entry->entry->author->email);

        // Non standard support
        if($entry->entry->source && $entry->entry->source->author->name)
            $this->__set('aname', (string)$entry->entry->source->author->name);
        if($entry->entry->source && $entry->entry->source->author->uri)
            $this->__set('aid', substr((string)$entry->entry->source->author->uri, 5));

        $this->__set('title', $this->getTitle($entry->entry->title));

        // Content
        if($entry->entry->summary && (string)$entry->entry->summary != '')
            $summary = '<p class="summary">'.(string)$entry->entry->summary.'</p>';
        else
            $summary = '';

        if($entry->entry && $entry->entry->content) {
            $content = $this->getContent($entry->entry->content);
        } elseif($summary == '')
            $content = __('');
        else
            $content = '';

        $content = $summary.$content;

        if($entry->entry->updated)
            $this->__set('updated', (string)$entry->entry->updated);
        else
            $this->__set('updated', gmdate(DATE_ISO8601));

        if($entry->entry->published)
            $this->__set('published', (string)$entry->entry->published);
        elseif($entry->entry->updated)
            $this->__set('published', (string)$entry->entry->updated);
        else
            $this->__set('published', gmdate(DATE_ISO8601));

        if($delay)
            $this->__set('delay', $delay);

        // Tags parsing
        if($entry->entry->category) {
            $td = new \Modl\TagDAO;
            $td->delete($this->__get('nodeid'));

            if($entry->entry->category->count() == 1
            && isset($entry->entry->category->attributes()->term)) {
                $tag = new \Modl\Tag;
                $tag->nodeid = $this->__get('nodeid');
                $tag->tag    = (string)$entry->entry->category->attributes()->term;
                $td->set($tag);
            } else {
                foreach($entry->entry->category as $cat) {
                    $tag = new \Modl\Tag;
                    $tag->nodeid = $this->__get('nodeid');
                    $tag->tag    = (string)$cat->attributes()->term;
                    $td->set($tag);
                }
            }
        }

        if(!isset($this->commentplace))
            $this->__set('commentplace', $this->origin);

        $this->__set('content', trim($content));
        $this->contentcleaned = purifyHTML(html_entity_decode($this->content));

        $extra = false;
        // We try to extract a picture
        $xml = \simplexml_load_string('<div>'.$this->contentcleaned.'</div>');
        if($xml) {
            $results = $xml->xpath('//img/@src');
            if(is_array($results) && !empty($results)) {
                $extra = (string)$results[0];
                $this->picture = true;
            }
        }

        $this->setAttachments($entry->entry->link, $extra);

        if($entry->entry->geoloc) {
            if($entry->entry->geoloc->lat != 0)
                $this->__set('lat', (string)$entry->entry->geoloc->lat);
            if($entry->entry->geoloc->lon != 0)
                $this->__set('lon', (string)$entry->entry->geoloc->lon);
        }
    }

    private function typeIsPicture($type) {
        return in_array($type, ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']);
    }

    private function typeIsLink($link) {
        return (isset($link['rel'])
        && in_array($link['rel'], ['related', 'alternate'])
        && Validator::url()->validate($link['href']));
    }

    private function setAttachments($links, $extra = false) {
        $l = [];

        foreach($links as $attachment) {
            $enc = [];
            $enc = (array)$attachment->attributes();
            $enc = $enc['@attributes'];
            array_push($l, $enc);

            if(array_key_exists('type', $enc)
            && $this->typeIsPicture($enc['type'])) {
                $this->picture = true;
            }

            if($enc['rel'] == 'alternate'
            && Validator::url()->validate($enc['href'])) $this->open = true;

            if((string)$attachment->attributes()->title == 'comments') {
                $substr = explode('?',substr((string)$attachment->attributes()->href, 5));
                $this->commentplace = reset($substr);
            }
        }

        if($extra) {
            array_push(
                $l,
                [
                    'rel' => 'enclosure',
                    'href' => $extra,
                    'type' => 'picture'
                ]);
        }

        if(!empty($l)) {
            $this->links = serialize($l);
        }
    }

    public function getAttachments()
    {
        $attachments = null;
        $this->picture = null;
        $this->openlink = null;

        if(isset($this->links)) {
            $links = unserialize($this->links);
            $attachments = [
                'pictures' => [],
                'files' => [],
                'links' => []
            ];

            foreach($links as $l) {
                // If the href is not a valid URL we skip
                if(!Validator::url()->validate($l['href'])) continue;

                // Prepare the switch
                $rel = isset($l['rel']) ? $l['rel'] : null;
                switch($rel) {
                    case 'enclosure':
                        if($this->typeIsPicture($l['type'])) {
                            array_push($attachments['pictures'], $l);

                            if($this->picture == null) {
                                $this->picture = $l['href'];
                            }
                        } elseif($l['type'] == 'picture' && $this->picture == null) {
                            $this->picture = $l['href'];
                        } else {
                            array_push($attachments['files'], $l);
                        }
                        break;

                    case 'related':
                        if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $l['href'], $match)) {
                            $this->youtube = $match[1];
                        }

                        array_push(
                            $attachments['links'],
                            [
                                'href' => $l['href'],
                                'url'  => parse_url($l['href']),
                                'rel'  => 'related'
                            ]
                        );
                        break;

                    case 'alternate':
                    default:
                        $this->openlink = $l['href'];
                        if(!$this->isMicroblog()) {
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

        if(empty($attachments['pictures'])) unset($attachments['pictures']);
        if(empty($attachments['files']))    unset($attachments['files']);
        if(empty($attachments['links']))    unset($attachments['links']);

        return $attachments;
    }

    public function getAttachment($link = false)
    {
        $attachments = $this->getAttachments();

        if(isset($attachments['pictures']) && !isset($attachments['links'])) {
            return $attachments['pictures'][0];
        }
        if(isset($attachments['files']) && !$link) {
            return $attachments['files'][0];
        }
        if(isset($attachments['links'])) {
            return $attachments['links'][0];
        }

        return false;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function getYoutube()
    {
        return $this->youtube;
    }

    public function getPlace()
    {
        return (isset($this->lat, $this->lon) && $this->lat != '' && $this->lon != '');
    }

    public function isMine()
    {
        $user = new \User();

        return ($this->aid == $user->getLogin()
        || $this->origin == $user->getLogin());
    }

    public function getUUID()
    {
        if(substr($this->nodeid, 10) == 'urn:uuid:') {
            return $this->nodeid;
        } else {
            return 'urn:uuid:'.generateUUID($this->nodeid);
        }
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

    public function getPublicUrl()
    {
        return $this->openlink;
    }

    public function getTags()
    {
        $td = new \Modl\TagDAO;
        $tags = $td->getTags($this->nodeid);
        if(is_array($tags)) {
            return array_map(function($tag) { return $tag->tag; }, $tags);
        }
    }

    public function getTagsImploded()
    {
        $tags = $this->getTags();
        if(is_array($tags)) {
            return implode(', ', $tags);
        }
    }

    public function isPublic() {
        return ($this->open);
    }
}

class ContactPostn extends Postn {
    public $jid;

    public $fn;
    public $name;

    public $privacy;

    public $phototype;
    public $photobin;

    public $nickname;

    function getContact() {
        $c = new Contact();
        $c->jid = $this->aid;
        $c->fn = $this->fn;
        $c->name = $this->name;
        $c->nickname = $this->nickname;
        $c->phototype = $this->phototype;
        $c->photobin = $this->photobin;

        return $c;
    }
}

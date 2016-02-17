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

    public $privacy;

    public $hash;

    private $youtube;

    public function __construct() {
        $this->hash = md5(openssl_random_pseudo_bytes(5));

        $this->_struct = '
        {
            "origin" :
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "node" :
                {"type":"string", "size":96, "mandatory":true, "key":true },
            "nodeid" :
                {"type":"string", "size":96, "mandatory":true, "key":true },
            "aname" :
                {"type":"string", "size":128 },
            "aid" :
                {"type":"string", "size":128 },
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

            "published" :
                {"type":"date" },
            "updated" :
                {"type":"date" },
            "delay" :
                {"type":"date" },

            "lat" :
                {"type":"string", "size":128 },
            "lon" :
                {"type":"string", "size":128 },

            "links" :
                {"type":"text" },
            "picture" :
                {"type":"int", "size":4 },
            "hash" :
                {"type":"string", "size":128 }
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

        $this->__set('title', (string)$entry->entry->title);

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

        $contentimg = $this->setAttachements($entry->entry->link);

        // Tags parsing
        if($entry->entry->category) {
            $td = new \Modl\TagDAO;

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

        if($contentimg != '')
            $content .= '<br />'.$contentimg;

        if(!isset($this->commentplace))
            $this->__set('commentplace', $this->origin);

        $this->__set('content', trim($content));
        $this->contentcleaned = purifyHTML(html_entity_decode($this->content));

        if($entry->entry->geoloc) {
            if($entry->entry->geoloc->lat != 0)
                $this->__set('lat', (string)$entry->entry->geoloc->lat);
            if($entry->entry->geoloc->lon != 0)
                $this->__set('lon', (string)$entry->entry->geoloc->lon);
        }
    }

    private function typeIsPicture($type) {
        return in_array($type, array('image/jpeg', 'image/png', 'image/jpg', 'image/gif'));
    }

    private function typeIsLink($type) {
        return $type == 'text/html';
    }

    private function setAttachements($links) {
        $contentimg = '';

        $l = array();

        foreach($links as $attachment) {
            $enc = array();
            $enc = (array)$attachment->attributes();
            $enc = $enc['@attributes'];
            array_push($l, $enc);

            if(array_key_exists('type', $enc)
            && $this->typeIsPicture($enc['type'])) {
                $this->picture = true;
            }

            if((string)$attachment->attributes()->title == 'comments') {
                $substr = explode('?',substr((string)$attachment->attributes()->href, 5));
                $this->commentplace = reset($substr);
            }
        }

        if(!empty($l))
            $this->links = serialize($l);

        return $contentimg;
    }

    public function getAttachements()
    {
        $attachements = null;
        $this->picture = null;

        if(isset($this->links)) {
            $attachements = array('pictures' => array(), 'files' => array(), 'links' => array());

            $links = unserialize($this->links);
            foreach($links as $l) {
                if(isset($l['type']) && $this->typeIsPicture($l['type'])) {
                    if($this->picture == null) {
                        $this->picture = $l['href'];
                    }
                    array_push($attachements['pictures'], $l);
                } elseif((isset($l['type']) && $this->typeIsLink($l['type'])
                || in_array($l['rel'], array('related', 'alternate')))
                && Validator::url()->validate($l['href'])) {
                    if($this->youtube == null
                    && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $l['href'], $match)) {
                        $this->youtube = $match[1];
                    }

                    array_push($attachements['links'], array('href' => $l['href'], 'url' => parse_url($l['href'])));
                } elseif(isset($l['rel']) && $l['rel'] == 'enclosure') {
                    array_push($attachements['files'], $l);
                }
            }
        }

        if(empty($attachements['pictures'])) unset($attachements['pictures']);
        if(empty($attachements['files']))    unset($attachements['files']);
        if(empty($attachements['links']))    unset($attachements['links']);

        return $attachements;
    }

    public function getAttachement()
    {
        $attachements = $this->getAttachements();
        if(isset($attachements['pictures']) && !isset($attachements['links'])) {
            return $attachements['pictures'][0];
        }
        if(isset($attachements['files'])) {
            return $attachements['files'][0];
        }
        if(isset($attachements['links'])) {
            return $attachements['links'][0];
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
        return ($this->contentraw != null);
    }

    public function isShort()
    {
        return (strlen($this->contentcleaned) < 700);
    }

    public function getPublicUrl()
    {
        if($this->isMicroblog()) {
            return \Route::urlize('blog', array($this->origin, $this->nodeid));
        } else {
            return \Route::urlize('node', array($this->origin, $this->node, $this->nodeid));
        }
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
        return (isset($this->privacy) && $this->privacy);
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

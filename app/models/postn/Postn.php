<?php

namespace Modl;

class Postn extends Model {
    public $origin;         // Where the post is comming from (jid or server)
    public $node;           // microblog or pubsub
    public $nodeid;         // the ID if the item

    public $aname;          // author name
    public $aid;            // author id
    public $aemail;         // author email

    public $title;          //
    public $content;        // The content
    public $contentcleaned; // The cleanned content

    public $commentplace;

    public $published;      //
    public $updated;        //
    public $delay;          //

    public $tags;           // Store the tags
    public $picture;        // Tell if the post contain embeded pictures

    public $lat;
    public $lon;

    public $links;

    public $privacy;

    public $hash;

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
            "tags" :
                {"type":"text" },
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
                    if($c->getName() == 'content') return $c->children()->asXML();
                    else return (string)$c->asXML();
                    break;
                case 'text':
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
            $this->tags = array();

            if($entry->entry->category->count() == 1
            && isset($entry->entry->category->attributes()->term))
                array_push($this->tags, (string)$entry->entry->category->attributes()->term);
            else
                foreach($entry->entry->category as $cat)
                    array_push($this->tags, (string)$cat->attributes()->term);
        }

        if(!empty($this->tags))
            $this->__set('tags', serialize($this->tags));

        if($contentimg != '')
            $content .= '<br />'.$contentimg;

        if(!isset($this->commentplace))
            $this->__set('commentplace', $this->origin);

        $this->__set('content', trim($content));
        //$this->__set('contentcleaned', prepareString(html_entity_decode($this->content)));
        $purifier = new \HTMLPurifier();
        $this->contentcleaned = $purifier->purify(html_entity_decode($this->content));

        if($entry->entry->geoloc) {
            if($entry->entry->geoloc->lat != 0)
                $this->__set('lat', (string)$entry->entry->geoloc->lat);
            if($entry->entry->geoloc->lon != 0)
                $this->__set('lon', (string)$entry->entry->geoloc->lon);
        }
    }

    private function typeIsPicture($type) {
        return in_array($type, array('image/jpeg', 'image/png', 'image/jpg'));
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

        if(isset($this->links)) {
            $attachements = array('pictures' => array(), 'files' => array(), 'links' => array());

            $links = unserialize($this->links);
            foreach($links as $l) {
                switch($l['rel']) {
                    case 'enclosure' :
                        if($this->typeIsPicture($l['type'])) {
                            array_push($attachements['pictures'], $l);
                        } else {
                            array_push($attachements['files'], $l);
                        }
                        break;
                    case 'alternate' :
                        array_push($attachements['links'], array('href' => $l['href'], 'url' => parse_url($l['href'])));
                        break;
                }
            }
        }

        if(empty($attachements['pictures'])) unset($attachements['pictures']);
        if(empty($attachements['files']))    unset($attachements['files']);
        if(empty($attachements['links']))    unset($attachements['links']);

        return $attachements;
    }

    public function getPicture()
    {
        $attachements = $this->getAttachements();
        if(is_array($attachements)
        && array_key_exists('pictures', $attachements)) {
            return $attachements['pictures'][0]['href'];
        }
    }

    public function getPlace() {
        if(isset($this->lat, $this->lon) && $this->lat != '' && $this->lon != '') {
            return true;
        }
        else
            return false;
    }

    public function isMine() {
        $user = new \User();

        if($this->aid == $user->getLogin()
        || $this->origin == $user->getLogin())
            return true;
        else
            return false;
    }

    public function isMicroblog() {
        if($this->node == "urn:xmpp:microblog:0")
            return true;
        else
            return false;
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

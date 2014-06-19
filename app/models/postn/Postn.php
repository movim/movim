<?php

namespace Modl;

class Postn extends Model {
    public $session;

    public $jid;            // Where the post is comming from (jid or server)
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

    public $tags;

    public $lat;
    public $lon;
    
    public $links;
    
    public $privacy;
    
    public $hash;
    
    public function __construct() {
        $this->hash = md5(openssl_random_pseudo_bytes(5));
        
        $this->_struct = '
        {
            "session" : 
                {"type":"string", "size":64, "mandatory":true, "key":true },
            "jid" : 
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
            "tags" : 
                {"type":"text" },
            "hash" : 
                {"type":"string", "size":128 }
        }';
        
        parent::__construct();
    }
    
    public function set($item, $from, $delay = false, $node = false) {
        $user = new \User();
        $this->session = $user->getLogin();

        if($item->item)
            $entry = $item->item;
        else
            $entry = $item;
        
        $this->jid     = $from;
        
        if($node)
            $this->node     = $node;
        else
            $this->node     = (string)$item->attributes()->node;
        
        $this->nodeid   = (string)$entry->attributes()->id;
        
        if($entry->entry->id)
            $this->nodeid = (string)$entry->entry->id;

        // Get some informations on the author
        if($entry->entry->author->name)
            $this->aname   = (string)$entry->entry->author->name;
        if($entry->entry->author->uri)
            $this->aid     = substr((string)$entry->entry->author->uri, 5);
        if($entry->entry->author->email)
            $this->aemail     = (string)$entry->entry->author->email;
            
        // Non standard support
        if($entry->entry->source && $entry->entry->source->author->name)
            $this->aname   = (string)$entry->entry->source->author->name;
        if($entry->entry->source && $entry->entry->source->author->uri)
            $this->aid     = substr((string)$entry->entry->source->author->uri, 5);
            
        $this->title    = (string)$entry->entry->title;
        
        // Content
        if($entry->entry->summary && (string)$entry->entry->summary != '')
            $summary = '<p class="summary">'.(string)$entry->entry->summary.'</p>';
        else
            $summary = '';
            
        if($entry->entry && $entry->entry->content) {
            if((string)$entry->entry->content->attributes()->type == 'text')
                $content = (string)$entry->entry->content;
            elseif(
                (string)$entry->entry->content->attributes()->type == ('html' || 'xhtml')
                //&& $entry->entry->content->html
                //&& $entry->entry->content->html->body
                ) {
                $content = (string)$entry->entry->content/*->html->body*/->asXML();
            } else
                $content = (string)$entry->entry->content;
        } elseif($summary == '')
            $content = t('Content not found');
        else
            $content = '';
        
        $content = $summary.$content;

        if($entry->entry->updated)    
            $this->updated   = (string)$entry->entry->updated;
        else
            $this->updated   = date(DATE_ISO8601);
        
        if($entry->entry->published)    
            $this->published = (string)$entry->entry->published;
        elseif($entry->entry->updated)
            $this->published = (string)$entry->entry->updated;
        else
            $this->published = date(DATE_ISO8601);
        
        if($delay)
            $this->delay     = $delay;
            
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
            $this->tags = serialize($this->tags);
        
        if($contentimg != '')
            $content .= '<br />'.$contentimg;
            
        if(!isset($this->commentplace))
            $this->commentplace = $this->jid;
            
        $this->content = trim($content);
        $this->contentcleaned = prepareString(html_entity_decode($this->content));
        
        if($entry->entry->geoloc) {
            if($entry->entry->geoloc->lat != 0)
                $this->lat = (string)$entry->entry->geoloc->lat;
            if($entry->entry->geoloc->lon != 0)
                $this->lon = (string)$entry->entry->geoloc->lon;
        }
    }
    
    private function setAttachements($links) {
        $contentimg = '';
        
        $l = array();
        
        foreach($links as $attachment) {
            $enc = array();
            $enc = (array)$attachment->attributes();
            $enc = $enc['@attributes'];
            array_push($l, $enc); 

            
            if((string)$attachment->attributes()->title == 'comments') {
                $substr = explode('?',substr((string)$attachment->attributes()->href, 5));
                $this->commentplace = reset($substr);
            }
        }
        
        if(!empty($l))
            $this->links = serialize($l);
        
        return $contentimg;
    }

    public function getPlace() {
        if(isset($this->lat, $this->lon) && $this->lat != '' && $this->lon != '') {
            return true;
        }
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

<?php

namespace Moxl\Stanza;

use Ramsey\Uuid\Uuid;

class PubsubAtom {
    public $id;
    public $name;
    public $jid;
    public $content;
    public $title;
    public $link;
    public $image;
    public $contentxhtml = false;

    public $to;
    public $node;

    public $geo = false;
    public $comments = false;

    public $tags = array();

    public $published = false;

    public function __construct() {
        $this->id = (string)Uuid::uuid4();
    }

    public function enableComments() {
        $this->comments = true;
    }

    public function getDom() {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        //$entry = $dom->createElementNS('http://www.w3.org/2005/Atom', 'entry');
        $entry = $dom->createElement('entry');
        $dom->appendChild($entry);
        $entry->appendChild($dom->createElement('id', $this->id));

        if($this->title) {
            $entry->appendChild($dom->createElement('title', $this->title));
        }

        $author = $dom->createElement('author');
        $author->appendChild($dom->createElement('name', $this->name));
        $author->appendChild($dom->createElement('uri', 'xmpp:'.$this->jid));
        $entry->appendChild($author);

        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'application/atom+xml');
        $link->setAttribute('href', 'xmpp:'.$this->to.'?;node='.$this->id.';item='.$this->id);
        $entry->appendChild($link);

        if($this->comments) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'replies');
            $link->setAttribute('title', 'comments');
            $link->setAttribute('href', 'xmpp:'.$this->jid.'?;node=urn:xmpp:microblog:0:comments/'.$this->id);
            $entry->appendChild($link);
        }

        if($this->link) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'related');
            $link->setAttribute('href', $this->link);
            $entry->appendChild($link);
        }

        if($this->image && is_array($this->image)) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'enclosure');
            $link->setAttribute('href', $this->image['href']);
            if($this->image['type'] != null)
                $link->setAttribute('type', $this->image['type']);
            if($this->image['title'] != null)
                $link->setAttribute('title', $this->image['title']);
            $entry->appendChild($link);
        }

        /*if($this->geo) {
            $xml .= '
                    <geoloc xmlns="http://jabber.org/protocol/geoloc">
                        <lat>'.$this->geo['latitude'].'</lat>
                        <lon>'.$this->geo['longitude'].'</lon>
                        <altitude>'.$this->geo['altitude'].'</altitude>
                        <country>'.$this->geo['country'].'</country>
                        <countrycode>'.$this->geo['countrycode'].'</countrycode>
                        <region>'.$this->geo['region'].'</region>
                        <postalcode>'.$this->geo['postalcode'].'</postalcode>
                        <locality>'.$this->geo['locality'].'</locality>
                        <street>'.$this->geo['street'].'</street>
                        <building>'.$this->geo['building'].'</building>
                        <text>'.$this->geo['text'].'</text>
                        <uri>'.$this->geo['uri'].'</uri>
                        <timestamp>'.date('c').'</timestamp>
                    </geoloc>';
        }*/

        if($this->content) {
            $content_raw = $dom->createElement('content', $this->content);
            $content_raw->setAttribute('type', 'text');
            $entry->appendChild($content_raw);
        }

        if($this->contentxhtml) {
            $content = $dom->createElement('content');
            $div = $dom->createElement('div');
            $content->appendChild($div);
            $content->setAttribute('type', 'xhtml');
            $entry->appendChild($content);

            $f = $dom->createDocumentFragment();
            $f->appendXML($this->contentxhtml);
            $div->appendChild($f);
            $div->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
        }

        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', $this->to.'?;node='.$this->node.';item='.$this->id);
        $entry->appendChild($link);

        if($this->published != false) {
            $entry->appendChild($dom->createElement('published', date(DATE_ISO8601, $this->published)));
        } else {
            $entry->appendChild($dom->createElement('published', gmdate(DATE_ISO8601)));
        }

        foreach($this->tags as $tag) {
            $category = $dom->createElement('category');
            $entry->appendChild($category);
            $category->setAttribute('term', $tag);
        }

        $entry->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $entry->appendChild($dom->createElement('updated', gmdate(DATE_ISO8601)));

        return $dom->documentElement;
    }

    public function __toString() {
        return $dom->saveXML($this->getDom());
    }
}

<?php

namespace Moxl\Stanza;

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

    public function __construct() {
        $this->id = md5(openssl_random_pseudo_bytes(5));
    }

    public function enableComments() {
        $this->comments = true;
    }

    public function __toString() {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $entry = $dom->createElementNS('http://www.w3.org/2005/Atom', 'entry');
        $dom->appendChild($entry);
        $entry->appendChild($dom->createElement('id', $this->id));

        if($this->title) {
            $entry->appendChild($dom->createElement('title', $this->title));
        }

        $author = $dom->createElement('author');
        $author->appendChild($dom->createElement('name', $this->name));
        $author->appendChild($dom->createElement('uri', 'xmpp:'.$this->jid));
        $entry->appendChild($author);

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

        if($this->geo) {
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
        }

        if($this->content) {
            $content_raw = $dom->createElement('content', $this->content);
            $content_raw->setAttribute('type', 'text');
            $entry->appendChild($content_raw);
        }

        if($this->contentxhtml) {
            $content = $dom->createElement('content');
            $content->setAttribute('type', 'xhtml');
            $div = $dom->createElementNS('http://www.w3.org/1999/xhtml', 'div');
            $content->appendChild($div);
            $entry->appendChild($content);

            $f = $dom->createDocumentFragment();
            $f->appendXML($this->contentxhtml);
            $div->appendChild($f);
        }

        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', $this->to.'?;node='.$this->node.';item='.$this->id);
        $entry->appendChild($link);

        $entry->appendChild($dom->createElement('published', gmdate(DATE_ISO8601)));
        $entry->appendChild($dom->createElement('updated', gmdate(DATE_ISO8601)));

        return $dom->saveXML($dom->documentElement);
    }
}

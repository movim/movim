<?php

class Syndication extends \Movim\Widget\Base
{
    function load()
    {

    }

    function display()
    {
        ob_clean();

        $pd = new \modl\PostnDAO();
        $cd = new \modl\ContactDAO();
        $id = new \Modl\ItemDAO;

        if(!$this->get('s')) {
            return;
        }

        $from = $this->get('s');
        $item = $contact = null;

        if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $node = 'urn:xmpp:microblog:0';
            $contact = $cd->get($from);
        } elseif(!$this->get('n')) {
            return;
        } else {
            $node = $this->get('n');
            $item = $id->getItem($from, $node);
        }

        $messages = $pd->getPublic($from, $node, 0, 20);
        header("Content-Type: application/atom+xml; charset=UTF-8");

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $feed = $dom->createElementNS('http://www.w3.org/2005/Atom', 'feed');
        $dom->appendChild($feed);

        $feed->appendChild($dom->createElement('updated', date('c')));

        $feed->appendChild($self = $dom->createElement('link'));
        $self->setAttribute('rel', 'self');

        if($contact != null) {
            $feed->appendChild($dom->createElement('title', __('feed.title', $contact->getTrueName())));

            $feed->appendChild($author = $dom->createElement('author'));
            $author->appendChild($dom->createElement('name', $contact->getTrueName()));
            $author->appendChild($dom->createElement('uri', Route::urlize('blog', [$from])));

            $feed->appendChild($dom->createElement('logo', $contact->getPhoto('l')));

            $self->setAttribute('href', Route::urlize('feed', [$from]));
        }

        if($item != null) {
            if($item->name) {
                $feed->appendChild($dom->createElement('title', $item->name));
            } else {
                $feed->appendChild($dom->createElement('title', $item->node));
            }

            if($item->description) {
                $feed->appendChild($dom->createElement('subtitle', $item->description));
            } else {
                $feed->appendChild($dom->createElement('subtitle', $item->server));
            }

            $self->setAttribute('href', Route::urlize('feed', [$from, $node]));
        }

        $feed->appendChild($generator = $dom->createElement('generator', 'Movim'));
        $generator->setAttribute('uri', 'https://movim.eu');
        $generator->setAttribute('version', APP_VERSION);

        foreach($messages as $message) {
            $feed->appendChild($entry = $dom->createElement('entry'));

            if($message->title) {
                $entry->appendChild($dom->createElement('title', $message->title));
            } else {
                $entry->appendChild($dom->createElement('title', __('post.default_title')));
            }

            $entry->appendChild($dom->createElement('id', $message->getUUID()));
            $entry->appendChild($dom->createElement('updated', date('c', strtotime($message->updated))));

            $entry->appendChild($content = $dom->createElement('content'));
            $content->appendChild($div = $dom->createElementNS('http://www.w3.org/1999/xhtml', 'div'));
            $content->setAttribute('type', 'xhtml');

            $f = $dom->createDocumentFragment();
            $f->appendXML($message->contentcleaned);
            $div->appendChild($f);

            $attachments = $message->getAttachments();

            if(isset($attachments['pictures'])) {
                foreach($attachments['pictures'] as $value) {
                    $entry->appendChild($link = $dom->createElement('link'));
                    $link->setAttribute('rel', 'enclosure');
                    $link->setAttribute('type', $value['type']);
                    $link->setAttribute('href', $value['href']);
                }
            }

            if(isset($attachments['files'])) {
                foreach($attachments['files'] as $value) {
                    $entry->appendChild($link = $dom->createElement('link'));
                    $link->setAttribute('rel', 'enclosure');
                    $link->setAttribute('type', $value['type']);
                    $link->setAttribute('href', $value['href']);
                }
            }

            if(isset($attachments['links'])) {
                foreach($attachments['links'] as $value) {
                    $entry->appendChild($link = $dom->createElement('link'));
                    $link->setAttribute('rel', 'alternate');
                    $link->setAttribute('href', $value['href']);
                }
            }

            $entry->appendChild($link = $dom->createElement('link'));
            $link->setAttribute('rel', 'alternate');
            $link->setAttribute('type', 'text/html');
            $link->setAttribute('href', $message->getPublicUrl());
        }

        echo $dom->saveXML();
        exit;
    }
}

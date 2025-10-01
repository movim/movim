<?php

namespace Moxl\Stanza;

use App\Post;

class PubsubAtom
{
    public $id;
    public $name;
    public $jid;
    public $content;
    public $title;

    public $links = [];
    public $images = [];

    public $contentxhtml = false;

    public $repost;
    public $reply;

    public $to;
    public $node;

    public $geo = false;
    public $comments = false;
    public $open = false;

    public $tags = [];

    public $published = false;

    public function __construct()
    {
        $this->id = generateUUID();
    }

    public function enableComments($server = true)
    {
        $this->comments = $server;
    }

    public function isOpen()
    {
        $this->open = true;
    }

    public function getDom()
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $entry = $dom->createElement('entry');
        $dom->appendChild($entry);
        $entry->appendChild($dom->createElement('id', $this->id));

        if ($this->title) {
            $entry->appendChild($dom->createElement('title', $this->title));
        }

        $author = $dom->createElement('author');
        if ($this->name) {
            $author->appendChild($dom->createElement('name', $this->name));
        }
        $author->appendChild($dom->createElement('uri', 'xmpp:' . $this->jid));
        $entry->appendChild($author);

        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('href', 'xmpp:' . $this->to . '?;node=' . $this->node . ';item=' . $this->id);
        $entry->appendChild($link);

        if ($this->comments) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'replies');
            $link->setAttribute('title', 'comments');

            if ($this->repost) {
                $link->setAttribute('href', 'xmpp:' . $this->repost[0] . '?;node=' . Post::COMMENTS_NODE . '/' . $this->repost[2]);
            } elseif ($this->comments === true) {
                $link->setAttribute('href', 'xmpp:' . $this->to . '?;node=' . Post::COMMENTS_NODE . '/' . $this->id);
            } else {
                $link->setAttribute('href', 'xmpp:' . $this->comments . '?;node=' . Post::COMMENTS_NODE . '/' . $this->id);
            }

            $entry->appendChild($link);
        }

        if ($this->open) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'alternate');
            $link->setAttribute('type', 'text/html');
            $link->setAttribute('title', $this->title);

            // Not very elegant
            if ($this->node == Post::MICROBLOG_NODE) {
                $link->setAttribute('href', \Movim\Route::urlize('blog', [$this->to, $this->id]));
            } else {
                $link->setAttribute('href', \Movim\Route::urlize('community', [$this->to, $this->node, $this->id]));
            }

            $entry->appendChild($link);
        }

        foreach ($this->links as $link) {
            if (is_array($link)) {
                $linke = $dom->createElement('link');
                $linke->setAttribute('rel', 'related');
                $linke->setAttribute('href', $link['href']);
                if ($link['type'] != null) {
                    $linke->setAttribute('type', $link['type']);
                }
                if ($link['title'] != null) {
                    $linke->setAttribute('title', $link['title']);
                }
                if ($link['description'] != null) {
                    $linke->setAttribute('description', $link['description']);
                }
                if ($link['logo'] != null) {
                    $linke->setAttribute('logo', $link['logo']);
                }
                $entry->appendChild($linke);
            }
        }

        if ($this->repost) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel', 'via');
            $link->setAttribute('href', 'xmpp:' . $this->repost[0] . '?;node=' . $this->repost[1] . ';item=' . $this->repost[2]);
            $entry->appendChild($link);
        }

        if ($this->reply) {
            $thr = $dom->createElement('thr:in-reply-to');
            $thr->setAttribute('href', $this->reply);
            $entry->appendChild($thr);
        }

        foreach ($this->images as $image) {
            if (is_array($image)) {
                $link = $dom->createElement('link');
                $link->setAttribute('rel', 'enclosure');
                $link->setAttribute('href', $image['href']);
                if ($image['type'] != null) {
                    $link->setAttribute('type', $image['type']);
                }
                if ($image['title'] != null) {
                    $link->setAttribute('title', $image['title']);
                }
                $entry->appendChild($link);
            }
        }

        if ($this->content) {
            $contentRaw = $dom->createElement('content');
            $contentRaw->appendChild($dom->createTextNode($this->content));
            $contentRaw->setAttribute('type', 'text');
            $entry->appendChild($contentRaw);
        }

        if ($this->contentxhtml) {
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

        if ($this->published != false) {
            $entry->appendChild($dom->createElement('published', date('c', $this->published)));
        } else {
            $entry->appendChild($dom->createElement('published', gmdate('c')));
        }

        if (is_array($this->tags)) {
            foreach ($this->tags as $tag) {
                $category = $dom->createElement('category');
                $entry->appendChild($category);
                $category->setAttribute('term', $tag);
            }
        }

        $entry->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        if ($this->reply) {
            $entry->setAttribute('xmlns:thr', 'http://purl.org/syndication/thread/1.0');
        }
        $entry->appendChild($dom->createElement('updated', gmdate('c')));

        return $dom->documentElement;
    }
}

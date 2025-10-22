<?php

namespace Moxl\Xec\Payload;

use App\Post as AppPost;
use Moxl\Xec\Action\Pubsub\GetItem;

class Post extends Payload
{
    private $testid = 'test_post';

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = (string)$parent->attributes()->from;
        $node = (string)$stanza->items->attributes()->node;

        if (
            $stanza->items->item
            && isset($stanza->items->item->entry)
            && (string)$stanza->items->item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom'
        ) {
            $delay = ($parent->delay)
                ? gmdate('Y-m-d H:i:s', strtotime((string)$parent->delay->attributes()->stamp))
                : false;

            $p = \App\Post::firstOrNew([
                'server' => $from,
                'node' =>  $node,
                'nodeid' => (string)$stanza->items->item->attributes()->id
            ]);
            $p->set($stanza->items->item, $delay);

            // We limit the very old posts (1 months old)
            if (
                strtotime($p->published) > mktime(0, 0, 0, gmdate("m") - 1, gmdate("d"), gmdate("Y"))
                && $p->nodeid != $this->testid
                && (($p->isComment() && isset($p->parent_id))
                    || !$p->isComment())
            ) {
                $p->save();

                $this->pack($p->id);

                if ($p->isStory()) {
                    $this->event('story');
                } else {
                    $this->deliver();
                }
            }
        } elseif ($stanza->items->retract) {
            \App\Post::where('nodeid', $stanza->items->retract->attributes()->id)
                ->where('server', $from)
                ->where('node', $node)
                ->delete();

            if ($node == AppPost::STORIES_NODE) {
                $this->event('story_retract');
                return;
            }

            $this->pack([
                'server' => $from,
                'node' => $node,
                'nodeid' => (string)$stanza->items->retract->attributes()->id
            ]);
            $this->method('retract');
            $this->deliver();
        } elseif (
            $stanza->items->item && isset($stanza->items->item->attributes()->id)
            && !filter_var($from, FILTER_VALIDATE_EMAIL)
        ) {
            // In this case we only get the header, so we request the full content
            $id = (string)$stanza->items->item->attributes()->id;

            if (
                \App\Post::where('server', $from)
                ->where('node', $node)
                ->where('nodeid', $id)
                ->count() == 0
                && $id != $this->testid
            ) {
                $d = new GetItem;
                $d->setTo($from)
                    ->setNode($node)
                    ->setId($id)
                    ->request();
            }
        }
    }
}

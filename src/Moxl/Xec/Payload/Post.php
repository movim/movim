<?php

namespace Moxl\Xec\Payload;

use App\BookmarksDirectory;
use App\Post as AppPost;
use Moxl\Stanza\Bookmark2;
use Moxl\Xec\Action\Pubsub\GetItem;

class Post extends Payload
{
    private $testid = 'test_post';

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if (! $stanza->items || ! $stanza->items->item) {
            return;
        }

        $from = (string) $parent->attributes()->from;
        $node = (string) $stanza->items->attributes()->node;

        if (
            $stanza->items->item
            && isset($stanza->items->item->entry)
            && (string) $stanza->items->item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom'
        ) {
            $delay = ($parent->delay)
                ? gmdate('Y-m-d H:i:s', strtotime((string) $parent->delay->attributes()->stamp))
                : false;

            $p = AppPost::firstOrNew([
                'server' => $from,
                'node' => $node,
                'nodeid' => (string) $stanza->items->item->attributes()->id,
            ]);
            $p->set($stanza->items->item, $delay);

            // We limit the very old posts (1 months old)
            if (
                strtotime($p->published) > mktime(0, 0, 0, gmdate('m') - 1, gmdate('d'), gmdate('Y'))
                && $p->nodeid != $this->testid
                && (($p->isComment() && isset($p->parent_id))
                    || ! $p->isComment())
            ) {
                $p->save();

                $this->pack($p->id);

                if ($p->isStory()) {
                    $this->deliver('story');
                } else {
                    $this->deliver();
                }
            }
        } elseif ($stanza->items->retract) {
            // Ensure that we actually have a Space subscription to the node
            $subscription = $this->me->subscriptions()
                ->spaces()
                ->where('server', $from)
                ->where('node', $node)
                ->first();

            if ($subscription) {
                $subscription->spaceRooms()->where('conference', $stanza->items->retract->attributes()->id)->delete();
                $this->pack([
                    'server' => $from,
                    'node' => $node,
                    'nodeid' => (string) $stanza->items->retract->attributes()->id,
                ]);
                $this->deliver('space_deletedroom');

                // $this->deliver();
                return;
            }

            AppPost::where('nodeid', $stanza->items->retract->attributes()->id)
                ->where('server', $from)
                ->where('node', $node)
                ->delete();

            if ($node == AppPost::STORIES_NODE) {
                $this->deliver('story_retract');

                return;
            }

            $this->pack([
                'server' => $from,
                'node' => $node,
                'nodeid' => (string) $stanza->items->retract->attributes()->id,
            ]);
            $this->method('retract');
            $this->deliver();
        } elseif (
            $stanza->items->item && $stanza->items->item->directories
            && $stanza->items->item->directories->attributes()->xmlns == Bookmark2::HIERARCHY_NAMESPACE
        ) {
            $this->me->session->bookmarksDirectories()
                ->where('server', $from)
                ->where('node', $node)
                ->delete();

            $i = 0;

            foreach ($stanza->items->item->directories->directory as $directory) {
                $dir = new BookmarksDirectory;
                $dir->session_id = $this->me->session->id;
                $dir->server = $from;
                $dir->node = $node;
                $dir->id = (string) $directory->attributes()->id;
                $dir->title = (string) $directory->attributes()->title;
                $dir->order = $i;
                $dir->save();
                $i++;
            }

            $this->pack([
                'server' => $from,
                'node' => $node,
            ]);
            $this->deliver('space_directories');
        } elseif (
            $stanza->items->item && isset($stanza->items->item->attributes()->id)
            && ! filter_var($from, FILTER_VALIDATE_EMAIL)
        ) {
            // In this case we only get the header, so we request the full content
            $id = (string) $stanza->items->item->attributes()->id;

            if (
                AppPost::where('server', $from)
                    ->where('node', $node)
                    ->where('nodeid', $id)
                    ->count() == 0
                && $id != $this->testid
            ) {
                $d = new GetItem($this->me, sessionId: $this->sessionId);
                $d->setTo($from)
                    ->setNode($node)
                    ->setId($id)
                    ->fromPayload()
                    ->request();
            }
        }
    }
}

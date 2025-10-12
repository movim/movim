<?php

namespace Moxl\Xec\Action\Pubsub;

use Carbon\Carbon;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

use Movim\Image;
use Moxl\Stanza\Avatar;
use Psr\Http\Message\ResponseInterface;

class GetItems extends Action
{
    protected $_to;
    protected $_node;
    protected $_since;
    protected $_paging;
    protected $_after;
    protected $_before;
    protected $_skip;
    protected $_query;

    protected $_paginated = false;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_node, $this->_paging, $this->_after, $this->_before, $this->_skip, $this->_query);
    }

    public function setAfter($after)
    {
        $this->_after = $after;
        $this->_paginated = true;
        return $this;
    }

    public function setBefore($before)
    {
        $this->_before = $before;
        $this->_paginated = true;
        return $this;
    }

    public function setSkip($skip = 0)
    {
        $this->_skip = $skip;
        $this->_paginated = true;
        return $this;
    }

    public function setQuery($query)
    {
        $this->_query = $query;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $ids = [];
        $updateds = collect();

        foreach ($stanza->pubsub->items->item as $item) {
            if (
                isset($item->entry)
                && (string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom'
            ) {
                if (
                    $this->_since == null
                    || strtotime($this->_since) < strtotime($item->entry->published)
                ) {
                    $p = \App\Post::firstOrNew([
                        'server' => $this->_to,
                        'node' => $this->_node,
                        'nodeid' => (string)$item->attributes()->id
                    ]);
                    $p->set($item);
                    $p->save();

                    array_push($ids, $p->nodeid);
                    $updateds->push(new Carbon($p->updated));
                }
            } elseif (
                isset($item->metadata)
                && (string)$item->metadata->attributes()->xmlns == Avatar::NODE_METADATA
                && isset($item->metadata->info->attributes()->url)
            ) {
                requestAvatarUrl(
                    jid: $this->_to,
                    node: $this->_node,
                    url: (string)$item->metadata->info->attributes()->url
                );
            }
        }

        if (
            $this->_after
            || ($updateds->isNotEmpty() && $updateds->first()->isBefore($updateds->last()))
        ) {
            $ids = array_reverse($ids);
        }

        $first = $last = $count = null;

        if (
            $stanza->pubsub->set
            && $stanza->pubsub->set->attributes()->xmlns == 'http://jabber.org/protocol/rsm'
        ) {
            $first = (string)$stanza->pubsub->set->first;
            $last = (string)$stanza->pubsub->set->last;
            $count = (string)$stanza->pubsub->set->count;

            $info = \App\Info::where('server', $this->_to)
                ->where('node', $this->_node)
                ->first();

            if ($info) {
                $info->items = $count;
                $info->save();
            }
        }

        $this->pack([
            'server'    => $this->_to,
            'node'      => $this->_node,
            'ids'       => $ids,
            'first'     => ($this->_after) ? $last : $first,
            'last'      => ($this->_after) ? $first : $last,
            'count'     => $count,
            'paginated' => $this->_paginated,
            'before'    => $this->_before,
            'after'     => $this->_after,
            'query'     => $this->_query
        ]);

        $this->deliver();
    }

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorPresenceSubscriptionRequired(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();

        return false; // Don't proparage to the general error() handler
    }
}

<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

class GetItems extends Errors
{
    protected $_to;
    protected $_node;
    protected $_since;
    protected $_paging;
    protected $_after;
    protected $_before;
    protected $_skip;

    protected $_paginated = false;

    public function request()
    {
        $this->store();
        Pubsub::getItems($this->_to, $this->_node, $this->_paging, $this->_after, $this->_before, $this->_skip);
    }

    public function setAfter($after)
    {
        $this->_after = $after;
        $this->_paginated = true;
        return $this;
    }

    public function setBefore($before = 'empty')
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

    public function handle($stanza, $parent = false)
    {
        $ids = [];

        foreach($stanza->pubsub->items->item as $item) {
            if (isset($item->entry)
            && (string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom') {
                if ($this->_since == null
                || strtotime($this->_since) < strtotime($item->entry->published)) {
                    $p = \App\Post::firstOrNew([
                        'server' => $this->_to,
                        'node' => $this->_node,
                        'nodeid' => (string)$item->attributes()->id
                    ]);
                    $p->set($item);
                    $p->save();

                    array_push($ids, $p->nodeid);
                }
            }
        }

        $first = $last = $count = null;

        if ($stanza->pubsub->set
        && $stanza->pubsub->set->attributes()->xmlns == 'http://jabber.org/protocol/rsm') {
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
            'first'     => $first,
            'last'      => $last,
            'count'     => $count,
            'paginated' => $this->_paginated
        ]);

        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}

<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action\Pubsub\Errors;

use Movim\Image;

class GetItem extends Errors
{
    protected $_to;
    protected $_node;
    protected $_id;
    protected $_askreply;

    protected $_manual = false; // Use when we explicitely request an item
    protected $_parentid;

    public function request()
    {
        $this->store();
        Pubsub::getItem($this->_to, $this->_node, $this->_id);
    }

    public function setManual()
    {
        $this->_manual = true;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        if ($stanza->pubsub->items->item) {
            foreach ($stanza->pubsub->items->item as $item) {
                if (isset($item->entry)
                && (string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom') {
                    $p = \App\Post::firstOrNew([
                        'server' => $this->_to,
                        'node' => $this->_node,
                        'nodeid' => $this->_id
                    ]);
                    $p->set($item);

                    if (isset($this->_parentid)) {
                        $p->parent_id    = $this->_parentid;
                    }

                    if ($p->isComment() && !isset($p->parent_id)) {
                        return;
                    }

                    $p->save();

                    if (!$this->_manual) {
                        if (is_array($this->_askreply)) {
                            $this->pack(\App\Post::find($this->_askreply));
                            $this->deliver();
                        } else {
                            $this->pack($p);
                            $this->event('post', $this->packet);
                        }
                    }

                    $this->pack($p);
                    $this->deliver();
                } elseif (isset($item->metadata)
                && (string)$item->metadata->attributes()->xmlns == 'urn:xmpp:avatar:metadata'
                && isset($item->metadata->info)
                && isset($item->metadata->info->attributes()->url)) {
                    $i = \App\Info::where('server', $this->_to)
                                  ->where('node', $this->_node)
                                  ->first();

                    if ($i && $i->avatarhash !== (string)$item->metadata->info->attributes()->id) {
                        $p = new Image;
                        $p->fromURL((string)$item->metadata->info->attributes()->url);
                        $p->setKey((string)$item->metadata->info->attributes()->id);
                        $p->save();

                        $i->avatarhash = (string)$item->metadata->info->attributes()->id;
                        $i->save();

                        $this->method('avatar');
                        $this->pack([
                            'server' => $this->_to,
                            'node' => $this->_node
                        ]);
                        $this->deliver();
                    }
                }
            }
        // Don't handle the case if we try to retrieve the avatar
        } elseif ($this->_id != 'urn:xmpp:avatar:metadata') {
            $pd = new PostDelete;
            $pd->setTo($this->_to)
               ->setNode($this->_node)
               ->setId($this->_id);

            $pd->handle($stanza);
        }
    }

    public function errorItemNotFound($stanza, $parent = false)
    {
        $this->errorServiceUnavailable($stanza, $parent);
    }

    public function errorBadRequest($stanza, $parent = false)
    {
        $this->errorServiceUnavailable($stanza, $parent);
    }

    public function errorServiceUnavailable($stanza, $parent = false)
    {
        $pd = new PostDelete;
        $pd->setTo($this->_to)
           ->setNode($this->_node)
           ->setId($this->_id);

        $pd->handle($stanza);
    }
}

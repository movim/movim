<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;

use Moxl\Stanza\Avatar;
use Psr\Http\Message\ResponseInterface;

class GetItem extends Action
{
    protected $_to;
    protected $_node;
    protected $_id;
    protected ?int $_replypostid = null;

    protected $_manual = false; // Use when we explicitely request an item
    protected $_parentid;

    protected $_messagemid;

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

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->pubsub->items->item) {
            foreach ($stanza->pubsub->items->item as $item) {
                if (
                    isset($item->entry)
                    && (string)$item->entry->attributes()->xmlns == 'http://www.w3.org/2005/Atom'
                ) {
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
                    };

                    $p->save();

                    if ($this->_replypostid != null) {
                        $this->pack($this->_replypostid);
                        $this->deliver();
                    } elseif ($p->isStory()) {
                        $this->pack($p->id);
                        $this->event('story');
                    } elseif ($p->isComment()) {
                        $this->pack($p->id);
                        $this->event('post_comment_published');
                    } else {
                        $this->pack($p->id);
                        $this->event('post');
                    }

                    if ($this->_messagemid) {
                        $message = me()->messages()->where('mid', $this->_messagemid)->first();

                        if ($message) {
                            $message->postid = $p->id;
                            $message->save();

                            $this->method('messageresolved');
                            $this->pack($message);
                            $this->deliver();
                        }
                    }
                } elseif (
                    isset($item->metadata)
                    && (string)$item->metadata->attributes()->xmlns == Avatar::NODE_METADATA
                    && isset($item->metadata->info)
                    && isset($item->metadata->info->attributes()->url)
                ) {
                    requestAvatarUrl(
                        jid: $this->_to,
                        node: $this->_node,
                        url: (string)$item->metadata->info->attributes()->url
                    )->then(function (ResponseInterface $response) {
                        $this->method('avatar');
                        $this->pack([
                            'server' => $this->_to,
                            'node' => $this->_node
                        ]);
                        $this->deliver();
                    });
                }
            }
            // Don't handle the case if we try to retrieve the avatar
        } elseif ($this->_id != Avatar::NODE_METADATA) {
            $pd = new PostDelete;
            $pd->setTo($this->_to)
                ->setNode($this->_node)
                ->setId($this->_id);

            $pd->handle();
        }
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->errorServiceUnavailable($errorId, $message);
    }

    public function errorBadRequest(string $errorId, ?string $message = null)
    {
        $this->errorServiceUnavailable($errorId, $message);
    }

    public function errorPresenceSubscriptionRequired(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorServiceUnavailable(string $errorId, ?string $message = null)
    {
        $pd = new PostDelete;
        $pd->setTo($this->_to)
            ->setNode($this->_node)
            ->setId($this->_id);

        $pd->handle();
    }
}

<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\Pubsub\GetItem;

class GetItemsId extends Errors
{
    protected $_to;
    protected $_node;
    private $_forbidenIds = [
        'urn:xmpp:avatar:data',
        'urn:xmpp:avatar:metadata'
    ];

    public function request()
    {
        $this->store();
        Disco::items($this->_to, $this->_node);
    }

    public function handle($stanza, $parent = false)
    {
        $ids = [];

        foreach ($stanza->query->xpath('item') as $item) {
            $id = (string)$item->attributes()->name;
            if (!\App\Post::where('server', $this->_to)
                          ->where('node', $this->_node)
                          ->where('nodeid', $id)
                          ->count() > 0
            && !empty($id)
            && !in_array($id, $this->_forbidenIds)) {
                $gi = new GetItem;
                $gi->setTo($this->_to)
                   ->setNode($this->_node)
                   ->setId($id)
                   ->request();
            }

            array_push($ids, $id);
        }

        $this->pack(['server' => $this->_to, 'node' => $this->_node, 'ids' => $ids]);
        $this->deliver();
    }

    public function error($errorid, $message)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}

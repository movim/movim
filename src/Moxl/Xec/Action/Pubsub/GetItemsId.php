<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Pubsub\Errors;
use Moxl\Xec\Action\Pubsub\GetItem;

class GetItemsId extends Errors
{
    private $_to;
    private $_node;

    public function request()
    {
        $this->store();
        Disco::items($this->_to, $this->_node);
    }

    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setNode($node)
    {
        $this->_node = $node;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $ids = [];

        foreach($stanza->query->xpath('item') as $item) {
            $id = (string)$item->attributes()->name;
            if (!\App\Post::where('server', $this->_to)
                          ->where('node', $this->_node)
                          ->where('nodeid', $id)
                          ->count() > 0
            && !empty($id)) {
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

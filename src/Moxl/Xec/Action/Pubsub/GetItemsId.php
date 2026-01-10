<?php

namespace Moxl\Xec\Action\Pubsub;

use Moxl\Stanza\Disco;
use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\GetItem;

class GetItemsId extends Action
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
        $this->iq(Disco::items($this->_node), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
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
                $gi = new GetItem($this->me, sessionId: $this->sessionId);
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

    public function error(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}

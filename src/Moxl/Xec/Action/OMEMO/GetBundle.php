<?php

namespace Moxl\Xec\Action\OMEMO;

use Moxl\Xec\Action;
use Moxl\Stanza\OMEMO;
use App\Bundle;

class GetBundle extends Action
{
    protected $_to;
    protected $_id;
    protected bool $_notifyLast = false;

    public function request()
    {
        $this->store();
        OMEMO::getBundle(
            $this->_to,
            $this->_id
        );
    }

    public function notifyLast()
    {
        $this->_notifyLast = true;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->pubsub->items->item->bundle) {
            $bundle = new Bundle;
            $bundle->set($this->_to, $this->_id, $stanza->pubsub->items->item->bundle);

            $this->pack($bundle);
            $this->deliver();

            if ($this->_notifyLast) {
                $this->pack($this->_to);
                $this->method('last');
                $this->deliver();
            }
        }
    }

    public function error(string $errorId, ?string $message = null)
    {
        if ($this->_notifyLast) {
            $this->pack($this->_to);
            $this->method('last');
            $this->deliver();
        }
    }
}

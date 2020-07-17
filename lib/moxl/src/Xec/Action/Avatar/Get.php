<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Get extends Action
{
    protected $_to;
    protected $_node = false;

    public function request()
    {
        $this->store();
        Avatar::get($this->_to, $this->_node);
    }

    public function handle($stanza, $parent = false)
    {
        $contact = \App\Contact::firstOrNew(['id' => $this->_to]);
        $contact->photobin  = (string)$stanza->pubsub->items->item->data;
        $contact->createThumbnails();
        $contact->save();

        $this->pack($contact);
        $this->deliver();
    }
}

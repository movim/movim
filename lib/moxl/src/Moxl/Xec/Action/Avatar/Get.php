<?php

namespace Moxl\Xec\Action\Avatar;

use Moxl\Xec\Action;
use Moxl\Stanza\Avatar;

class Get extends Action
{
    protected $_to;

    public function request()
    {
        $this->store();
        Avatar::get($this->_to);
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
